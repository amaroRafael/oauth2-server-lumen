<?php namespace Rapiro\OAuth2Server\Storage;

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends BaseStorage implements ClientInterface
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
        $query = $this->getConnection()->table('oauth_clients')
                  ->select('oauth_clients.*')
                  ->where('oauth_clients.id', $clientId);

        if ($clientSecret !== null) {
            $query->where('oauth_clients.secret', $clientSecret);
        }

        if ($redirectUri) {
            $query->join('oauth_client_redirect_uris', 'oauth_clients.id', '=', 'oauth_client_redirect_uris.client_id')
                  ->select(['oauth_clients.*', 'oauth_client_redirect_uris.*'])
                  ->where('oauth_client_redirect_uris.redirect_uri', $redirectUri);
        }

        $result = $query->first();

        if (!is_null($result)) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $result->id,
                'name'  =>  $result->name,
            ]);

            return $client;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getBySession(SessionEntity $session)
    {
        $result = $this->getConnection()->table('oauth_clients')
                    ->select(['oauth_clients.id', 'oauth_clients.name'])
                    ->join('oauth_sessions', 'oauth_clients.id', '=', 'oauth_sessions.client_id')
                    ->where('oauth_sessions.id', $session->getId())
                    ->first();

        if (!is_null($result)) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $result->id,
                'name'  =>  $result->name,
            ]);

            return $client;
        }

        return;
    }
}
