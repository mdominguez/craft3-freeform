<?php
/**
 * Freeform for Craft
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2020, Solspace, Inc.
 * @link          https://docs.solspace.com/craft/freeform
 * @license       https://docs.solspace.com/license-agreement
 */

namespace Solspace\Freeform\Integrations\MailingLists;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Solspace\Freeform\Library\Exceptions\Integrations\IntegrationException;
use Solspace\Freeform\Library\Integrations\DataObjects\FieldObject;
use Solspace\Freeform\Library\Integrations\IntegrationStorageInterface;
use Solspace\Freeform\Library\Integrations\MailingLists\AbstractMailingListIntegration;
use Solspace\Freeform\Library\Integrations\MailingLists\DataObjects\ListObject;
use Solspace\Freeform\Library\Integrations\SettingBlueprint;

class MailChimp extends AbstractMailingListIntegration
{
    const SETTING_API_KEY         = 'api_key';
    const SETTING_DOUBLE_OPT_IN   = 'double_opt_in';
    const SETTING_DATA_CENTER     = 'data_center';
    const SETTING_INTEREST_GROUPS = 'interest_groups';

    const TITLE        = 'MailChimp';
    const LOG_CATEGORY = 'MailChimp';

    /** @var array */
    private $existingTags;

    /**
     * Returns a list of additional settings for this integration
     * Could be used for anything, like - AccessTokens
     *
     * @return SettingBlueprint[]
     */
    public static function getSettingBlueprints(): array
    {
        return [
            new SettingBlueprint(
                SettingBlueprint::TYPE_TEXT,
                self::SETTING_API_KEY,
                'API Key',
                'Enter your MailChimp API key here.',
                true
            ),
            new SettingBlueprint(
                SettingBlueprint::TYPE_BOOL,
                self::SETTING_DOUBLE_OPT_IN,
                'Use double opt-in?',
                '',
                false
            ),
            new SettingBlueprint(
                SettingBlueprint::TYPE_INTERNAL,
                self::SETTING_DATA_CENTER,
                'Data Center',
                'This will be fetched automatically upon authorizing your credentials.',
                false
            ),
            new SettingBlueprint(
                SettingBlueprint::TYPE_INTERNAL,
                self::SETTING_INTEREST_GROUPS,
                'Interest Groups',
                'This will be fetched automatically upon authorizing your credentials.',
                false
            ),
        ];
    }

    /**
     * Check if it's possible to connect to the API
     *
     * @return bool
     * @throws IntegrationException
     */
    public function checkConnection(): bool
    {
        try {
            $response = $this->get('/');
            $json     = \GuzzleHttp\json_decode((string)$response->getBody());

            if (isset($json->error) && !empty($json->error)) {
                throw new IntegrationException($json->error);
            }

            return isset($json->account_id) && !empty($json->account_id);
        } catch (RequestException $e) {
            $this->logErrorAndThrow($e);
        }
    }

    /**
     * Push emails to a specific mailing list for the service provider
     *
     * @param ListObject $mailingList
     * @param array      $emails
     * @param array      $mappedValues
     *
     * @return bool
     * @throws IntegrationException
     */
    public function pushEmails(ListObject $mailingList, array $emails, array $mappedValues): bool
    {
        $isDoubleOptIn = $this->getSetting(self::SETTING_DOUBLE_OPT_IN);
        $listId        = $mailingList->getId();

        foreach ($emails as $email) {
            $emailHash  = md5($email);
            $memberData = [
                'email_address' => $email,
                'status'        => $isDoubleOptIn ? 'pending' : 'subscribed',
            ];

            $marketingPermissions = $tags = [];
            foreach ($mappedValues as $key => $value) {
                if (preg_match("/gdpr___(.*)/", $key, $matches)) {
                    $marketingPermissions[] = [
                        'marketing_permission_id' => $matches[1],
                        'enabled'                 => !empty($value),
                    ];

                    unset($mappedValues[$key]);
                }

                if (preg_match("/tags___tags/", $key)) {
                    $tags = explode(',', $value);
                    $tags = array_map('trim', $tags);
                    $tags = array_filter($tags);

                    unset($mappedValues[$key]);
                }
            }

            if (!empty($mappedValues)) {
                $memberData['merge_fields'] = $mappedValues;
            }

            if (!empty($marketingPermissions)) {
                $memberData['marketing_permissions'] = $marketingPermissions;
            }

            try {
                $patchMemberData = $memberData;
                unset($patchMemberData['email_address']);
                $response = $this->patch("lists/$listId/members/$emailHash", ['json' => $patchMemberData]);
                $this->getHandler()->onAfterResponse($this, $response);
            } catch (RequestException $exception) {
                try {
                    $response = $this->post("lists/$listId/members", ['json' => $memberData]);
                    $this->getHandler()->onAfterResponse($this, $response);
                } catch (RequestException $e) {
                    $this->logErrorAndThrow($e);
                }
            }

            $this->manageTags($listId, $email, $tags);
        }

        return true;
    }

    /**
     * A method that initiates the authentication
     */
    public function initiateAuthentication()
    {
    }

    /**
     * Authorizes the application
     * Returns the access_token
     *
     * @return string
     * @throws IntegrationException
     */
    public function fetchAccessToken(): string
    {
        return $this->getSetting(self::SETTING_API_KEY);
    }

    /**
     * Perform anything necessary before this integration is saved
     *
     * @param IntegrationStorageInterface $model
     *
     * @throws IntegrationException
     */
    public function onBeforeSave(IntegrationStorageInterface $model)
    {
        if (preg_match('/([a-zA-Z]+[\d]+)$/', $this->getSetting(self::SETTING_API_KEY), $matches)) {
            $dataCenter = $matches[1];
            $this->setSetting(self::SETTING_DATA_CENTER, $dataCenter);
        } else {
            throw new IntegrationException('Could not detect data center for MailChimp');
        }

        $model->updateAccessToken($this->getSetting(self::SETTING_API_KEY));
        $model->updateSettings($this->getSettings());
    }

    /**
     * Makes an API call that fetches mailing lists
     * Builds ListObject objects based on the results
     * And returns them
     *
     * @return ListObject[]
     * @throws IntegrationException
     */
    protected function fetchLists(): array
    {
        try {
            $response = $this->get(
                '/lists',
                [
                    'query' => [
                        'fields' => 'lists.id,lists.name,lists.stats.member_count',
                        'count'  => 999,
                    ],
                ]
            );
        } catch (RequestException $e) {
            $this->logErrorAndThrow($e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new IntegrationException(
                $this->getTranslator()->translate(
                    'Could not fetch {serviceProvider} lists',
                    ['serviceProvider' => $this->getServiceProvider()]
                )
            );
        }

        $json = \GuzzleHttp\json_decode((string)$response->getBody());

        $lists = [];
        if (isset($json->lists)) {
            foreach ($json->lists as $list) {
                if (isset($list->id, $list->name)) {
                    $lists[] = new ListObject(
                        $this,
                        $list->id,
                        $list->name,
                        $this->fetchFields($list->id),
                        $list->stats->member_count
                    );
                }
            }
        }

        return $lists;
    }

    /**
     * Fetch all custom fields for each list
     *
     * @param string $listId
     *
     * @return FieldObject[]
     * @throws IntegrationException
     */
    protected function fetchFields($listId): array
    {
        try {
            $response = $this->get("/lists/$listId/merge-fields", ['query' => ['count' => 999]]);
        } catch (RequestException $e) {
            $this->logErrorAndThrow($e);
        }

        $json = \GuzzleHttp\json_decode((string)$response->getBody());

        $fieldList = [];
        if (isset($json->merge_fields)) {
            $fieldList = [];
            foreach ($json->merge_fields as $field) {
                switch ($field->type) {
                    case 'text':
                    case 'website':
                    case 'url':
                    case 'dropdown':
                    case 'radio':
                    case 'date':
                    case 'zip':
                        $type = FieldObject::TYPE_STRING;
                        break;

                    case 'number':
                    case 'phone':
                        $type = FieldObject::TYPE_NUMERIC;
                        break;

                    default:
                        $type = null;
                        break;
                }

                if (null === $type) {
                    continue;
                }

                $fieldList[] = new FieldObject(
                    $field->tag,
                    $field->name,
                    $type,
                    $field->required
                );
            }
        }

        // Fetch marketing permissions
        try {
            $response = $this->get(
                "/lists/$listId/members",
                [
                    'query' => [
                        'count'  => 1,
                        'fields' => ['members.id', 'members.marketing_permissions'],
                    ],
                ]
            );

            $json    = \GuzzleHttp\json_decode((string)$response->getBody());
            $members = $json->members ?? [];

            if (!count($members)) {
                try {
                    $tempResponse = $this->post(
                        "/lists/$listId/members",
                        [
                            'json' => [
                                'email_address' => rand(10000, 99999).'_temp@test.test',
                                'status'        => 'subscribed',
                            ],
                        ]
                    );

                    $tempJson = \GuzzleHttp\json_decode((string)$tempResponse->getBody());

                    $tempSubscriberHash   = $tempJson->id;
                    $marketingPermissions = $tempJson->marketing_permissions ?? [];

                    $this->delete("/lists/$listId/members/$tempSubscriberHash");
                } catch (RequestException $e) {
                    $marketingPermissions = [];
                }
            } else {
                $marketing            = reset($members);
                $marketingPermissions = $marketing->marketing_permissions ?? [];
            }

            foreach ($marketingPermissions as $permission) {
                $fieldList[] = new FieldObject(
                    'gdpr___'.$permission->marketing_permission_id,
                    $permission->text.' (GDPR)',
                    FieldObject::TYPE_BOOLEAN,
                    false
                );
            }

        } catch (RequestException $e) {
        }

        $fieldList[] = new FieldObject(
            'tags___tags',
            'Tags (Tags)',
            FieldObject::TYPE_STRING
        );

        return $fieldList;
    }

    /**
     * Returns the API root url without endpoints specified
     *
     * @return string
     * @throws IntegrationException
     */
    protected function getApiRootUrl(): string
    {
        $dataCenter = $this->getSetting(self::SETTING_DATA_CENTER);

        if (empty($dataCenter)) {
            throw new IntegrationException(
                $this->getTranslator()->translate('Could not detect data center for MailChimp')
            );
        }

        return "https://$dataCenter.api.mailchimp.com/3.0/";
    }

    /**
     * @param string $endpoint
     * @param array  $requestParams
     *
     * @return ResponseInterface
     */
    private function get(string $endpoint, array $requestParams = [])
    {
        return $this->generateAuthorizedClient($requestParams)->get($this->getEndpoint($endpoint));
    }

    /**
     * @param string $endpoint
     * @param array  $requestParams
     *
     * @return ResponseInterface
     */
    private function post(string $endpoint, array $requestParams = []): ResponseInterface
    {
        return $this->generateAuthorizedClient($requestParams)->post($this->getEndpoint($endpoint));
    }

    /**
     * @param string $endpoint
     * @param array  $requestParams
     *
     * @return ResponseInterface
     */
    private function patch(string $endpoint, array $requestParams = []): ResponseInterface
    {
        return $this->generateAuthorizedClient($requestParams)->patch($this->getEndpoint($endpoint));
    }

    /**
     * @param string $endpoint
     * @param array  $requestParams
     *
     * @return ResponseInterface
     */
    private function delete(string $endpoint, array $requestParams = []): ResponseInterface
    {
        return $this->generateAuthorizedClient($requestParams)->delete($this->getEndpoint($endpoint));
    }

    /**
     * @param array $requestParams
     *
     * @return Client
     */
    private function generateAuthorizedClient(array $requestParams = []): Client
    {
        $config = array_merge(
            ['auth' => ['mailchimp', $this->getAccessToken()]],
            $requestParams
        );

        return new Client($config);
    }

    /**
     * @param RequestException $e
     */
    private function logErrorAndThrow(RequestException $e)
    {
        $responseBody = (string)$e->getResponse()->getBody();
        $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

        throw new IntegrationException(
            $this->getTranslator()->translate('Could not connect to API endpoint')
        );
    }

    /**
     * Create tags and add them to a member.
     *
     * @param string $listId
     * @param string $email
     * @param array  $tags
     */
    private function addTagsForMember(string $listId, string $email, array $tags)
    {
        foreach ($tags as $tag) {
            $tagId = $this->getOrCreateTag($listId, $tag);

            try {
                $this->post("lists/$listId/segments/$tagId/members", ['json' => ['email_address' => $email]]);
            } catch (RequestException $exception) {
                $this->getLogger()->error("Could not add a MailChimp tag '$tag' to user $email");
            }
        }
    }

    /**
     * Delete tags for a member.
     *
     * @param string $listId
     * @param string $emailHash
     * @param array  $tagsToDelete
     */
    private function deleteTagsForMember(string $listId, string $emailHash, array $tagsToDelete)
    {
        foreach ($tagsToDelete as $tagId => $tagName) {
            try {
                $this->delete("lists/$listId/segments/$tagId/members/$emailHash");
            } catch (RequestException $deleteException) {
                $this->getLogger()->warning("Could not delete MailChimp tag '$tagName' (#$tagId)");
            }
        }
    }

    /**
     * @param string $listId
     *
     * @return array
     */
    private function fetchTags(string $listId): array
    {
        if (null === $this->existingTags) {
            try {
                $response = $this->get(
                    "lists/$listId/segments",
                    ['query' => ['fields' => 'segments.id,segments.name', 'count' => 999]]
                );
                $data     = \GuzzleHttp\json_decode((string)$response->getBody());

                $tags = [];
                foreach ($data->segments as $tag) {
                    $tags[$tag->id] = strtolower($tag->name);
                }

                $this->existingTags = $tags;
            } catch (RequestException $e) {
                $this->getLogger()->error('Could not fetch MailChimp tags');

                $this->existingTags = [];
            }
        }

        return $this->existingTags;
    }

    /**
     * @param string $listId
     * @param string $tagName
     *
     * @return string|int|null
     */
    private function getOrCreateTag(string $listId, string $tagName)
    {
        $existingTags     = $this->fetchTags($listId);
        $tagNameLowerCase = strtolower($tagName);
        if (\in_array($tagNameLowerCase, $existingTags, true)) {
            return array_search($tagNameLowerCase, $existingTags, true);
        }

        try {
            $response = $this->post("lists/$listId/segments", ['json' => ['name' => $tagName, 'static_segment' => []]]);
            $data     = \GuzzleHttp\json_decode((string)$response->getBody());

            return $data->id;
        } catch (RequestException $e) {
            $this->getLogger()->warning("Could not create a MailChimp tag '$tagName'");

            return null;
        }
    }

    /**
     * @param string $listId
     * @param string $email
     * @param array  $tags
     */
    private function manageTags(string $listId, string $email, array $tags)
    {
        $emailHash = md5(strtolower($email));

        try {
            $response = $this->get("lists/$listId/members/$emailHash/tags", ['query' => ['count' => 999]]);
            $data     = \GuzzleHttp\json_decode((string)$response->getBody());

            $memberTags = [];
            foreach ($data->tags as $tag) {
                $memberTags[$tag->id] = $tag->name;
            }

            $tagsToAdd = array_diff($tags, $memberTags);
            $this->addTagsForMember($listId, $email, $tagsToAdd);

            $tagsToDelete = array_diff($memberTags, $tags);
            $this->deleteTagsForMember($listId, $emailHash, $tagsToDelete);
        } catch (RequestException $e) {
        }
    }
}
