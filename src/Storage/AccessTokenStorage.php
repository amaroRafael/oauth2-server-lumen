<?php namespace Rapiro\OAuth2Server\Storage;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;

class AccessTokenStorage extends BaseStorage implements AccessTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $result = $this->getConnection()->table('oauth_access_tokens')
                    ->where('access_token', $token)
                    ->first();

        if (!is_null($result)) {
            $token = (new AccessTokenEntity($this->server))
                        ->setId($result->access_token)
                        ->setExpireTime($result->expire_time);

            return $token;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
        $result = $this->getConnection()->table('oauth_access_token_scopes')
                    ->select(['oauth_scopes.id', 'oauth_scopes.description'])
                    ->join('oauth_scopes', 'oauth_access_token_scopes.scope', '=', 'oauth_scopes.id')
                    ->where('access_token', $token->getId())
                    ->get();

        $response = [];

        if (count($result) > 0) {
            foreach ($result as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate([
                    'id'            =>  $row->id,
                    'description'   =>  $row->description,
                ]);
                $response[] = $scope;
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
        $this->getConnection()->table('oauth_access_tokens')
            ->insert([
                'access_token'  =>  $token,
                'session_id'    =>  $sessionId,
                'expire_time'   =>  $expireTime,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $this->getConnection()->table('oauth_access_token_scopes')
            ->insert([
                'access_token'  =>  $token->getId(),
                'scope'         =>  $scope->getId(),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AccessTokenEntity $token)
    {
        $this->getConnection()->table('oauth_access_tokens')
            ->where('access_token', $token->getId())
            ->delete();
    }
}
