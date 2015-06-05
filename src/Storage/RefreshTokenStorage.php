<?php namespace Rapiro\OAuth2Server\Storage;

use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\RefreshTokenInterface;

class RefreshTokenStorage extends BaseStorage implements RefreshTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $result = $this->getConnection()->table('oauth_refresh_tokens')
                    ->where('refresh_token', $token)
                    ->first();

        if (!is_null($result)) {
            $token = (new RefreshTokenEntity($this->server))
                        ->setId($result->refresh_token)
                        ->setExpireTime($result->expire_time)
                        ->setAccessTokenId($result->access_token);

            return $token;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $accessToken)
    {
        $this->getConnection()->table('oauth_refresh_tokens')
            ->insert([
                'refresh_token' =>  $token,
                'access_token'  =>  $accessToken,
                'expire_time'   =>  $expireTime,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
        $this->getConnection()->table('oauth_refresh_tokens')
            ->where('refresh_token', $token->getId())
            ->delete();
    }
}
