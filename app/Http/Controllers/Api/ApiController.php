<?php

namespace App\Http\Controllers\Api;

use App\Events\PrivateMessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Response;


/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="VWCore API"
 * )
 */
/**
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="VWCore dynamic host server"
 *  ),
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     description="Oauth2 security",
 *     scheme="bearer"
 * )
 */
class ApiController extends Controller
{
    public function videochat(Request $request)
    {
        broadcast(new PrivateMessageSent($request->all()))->toOthers();
    }
}

