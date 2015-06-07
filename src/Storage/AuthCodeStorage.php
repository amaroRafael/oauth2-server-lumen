<?php namespace Rapiro\OAuth2Server\Storage;

use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AuthCodeInterface;

final class AuthCodeStorage extends BaseStorage implements AuthCodeInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        $result = $this->getConnection()->table('oauth_auth_codes')
                    ->where('auth_code', $code)
                    ->where('expire_time', '>=', time())
                    ->first();

        if (!is_null($result)) {
            $token = new AuthCodeEntity($this->server);
            $token->setId($result->auth_code);
            $token->setRedirectUri($result->client_redirect_uri);
            $token->setExpireTime($result->expire_time);

            return $token;
        }

        return;
    }

    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        $this->getConnection()->table('oauth_auth_codes')
            ->insert([
                'auth_code'            =>  $token,
                'client_redirect_uri'  =>  $redirectUri,
                'session_id'           =>  $sessionId,
                'expire_time'          =>  $expireTime,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $result = $this->getConnection()->table('oauth_auth_code_scopes')
                    ->select(['oauth_scopes.id', 'oauth_scopes.description'])
                    ->join('oauth_scopes', 'oauth_auth_code_scopes.scope', '=', 'oauth_scopes.id')
                    ->where('auth_code', $token->getId())
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
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        $this->getConnection()->table('oauth_auth_code_scopes')
            ->insert([
                'auth_code' =>  $token->getId(),
                'scope'     =>  $scope->getId(),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AuthCodeEntity $token)
    {
        $this->getConnection()->table('oauth_auth_codes')
            ->where('auth_code', $token->getId())
            ->delete();
    }
}
