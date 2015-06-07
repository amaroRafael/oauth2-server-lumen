<?php namespace Rapiro\OAuth2Server\Storage;

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\ClientInterface;

final class ClientStorage extends BaseStorage implements ClientInterface
{
    /**
     * @var bool
     */
    protected $limitClientsToGrants = false;

    /**
     * @param Resolver $connection
     * @param bool $limitClientsToGrants
     */
    public function __construct(Resolver $resolver, $limitClientsToGrants = false)
    {
        parent::__construct($resolver);
        $this->limitClientsToGrants = $limitClientsToGrants;
    }

    /**
     * @return bool
     */
    public function areClientsLimitedToGrants()
    {
        return $this->limitClientsToGrants;
    }

    /**
     * @param bool $limit whether or not to limit clients to grants
     */
    public function limitClientsToGrants($limit = false)
    {
        $this->limitClientsToGrants = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        if (!is_null($redirectUri) && is_null($clientSecret)) {
            $query = $this->getConnection()->table('oauth_clients')
                ->select(
                    'oauth_clients.id as id',
                    'oauth_clients.secret as secret',
                    'oauth_client_redirect_uris.redirect_uri as redirect_uri',
                    'oauth_clients.name as name')
                ->join('oauth_client_redirect_uris', 'oauth_clients.id', '=', 'oauth_client_redirect_uris.client_id')
                ->where('oauth_clients.id', $clientId)
                ->where('oauth_client_redirect_uris.redirect_uri', $redirectUri);
        } elseif (!is_null($clientSecret) && is_null($redirectUri)) {
            $query = $this->getConnection()->table('oauth_clients')
                ->select(
                    'oauth_clients.id as id',
                    'oauth_clients.secret as secret',
                    'oauth_clients.name as name')
                ->where('oauth_clients.id', $clientId)
                ->where('oauth_clients.secret', $clientSecret);
        } elseif (!is_null($clientSecret) && !is_null($redirectUri)) {
            $query = $this->getConnection()->table('oauth_clients')
                ->select(
                    'oauth_clients.id as id',
                    'oauth_clients.secret as secret',
                    'oauth_client_redirect_uris.redirect_uri as redirect_uri',
                    'oauth_clients.name as name')
                ->join('oauth_client_redirect_uris', 'oauth_clients.id', '=', 'oauth_client_redirect_uris.client_id')
                ->where('oauth_clients.id', $clientId)
                ->where('oauth_clients.secret', $clientSecret)
                ->where('oauth_client_redirect_uris.redirect_uri', $redirectUri);
        }

        if ($this->limitClientsToGrants === true and !is_null($grantType)) {
            $query = $query->join('oauth_client_grants', 'oauth_clients.id', '=', 'oauth_client_grants.client_id')
                ->join('oauth_grants', 'oauth_grants.id', '=', 'oauth_client_grants.grant_id')
                ->where('oauth_grants.id', $grantType);
        }

        $result = $query->first();

        if (!is_null($result)) {
            return $this->hydrateEntity($result);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getBySession(SessionEntity $session)
    {
        $result = $this->getConnection()->table('oauth_clients')
                    ->select(['oauth_clients.id', 'oauth_clients.secret', 'oauth_clients.name'])
                    ->join('oauth_sessions', 'oauth_clients.id', '=', 'oauth_sessions.client_id')
                    ->where('oauth_sessions.id', $session->getId())
                    ->first();

        if (!is_null($result)) {
            return $this->hydrateEntity($result);
        }

        return;
    }

    /**
     * @param $result
     * @return \League\OAuth2\Server\Entity\ClientEntity
     */
    protected function hydrateEntity($result)
    {
        $client = new ClientEntity($this->server);
        $client->hydrate([
            'id'          => $result->id,
            'name'        => $result->name,
            'secret'      => $result->secret,
            'redirectUri' => (isset($result->redirect_uri) ? $result->redirect_uri : null)
        ]);

        return $client;
    }
}
