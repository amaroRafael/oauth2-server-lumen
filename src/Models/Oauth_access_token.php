<?php  namespace Rapiro\Models;
/**
 * Created by PhpStorm.
 * User: ramaro
 * Date: 6/3/15
 * Time: 9:54 PM
 */
use Illuminate\Database\Eloquent\Model;

final class Oauth_access_token extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['access_token', 'session_id', 'expire_time'];

}