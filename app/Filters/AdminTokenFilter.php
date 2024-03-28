<?php

namespace App\Filters;

use App\Libraries\JwtService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminTokenFilter implements FilterInterface
{
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if the request needs authentication
        if ($request->hasHeader('Authorization')) {
            $accessToken = $request->getHeader('Authorization')->getValue();

            $jwtService = new JwtService();
            $decodedAccessToken = $jwtService->decodeToken($accessToken);

            if ($decodedAccessToken) {
                $request->user = $decodedAccessToken->data;
                return $request;
            } else {
                // Access token is expired or invalid, try refreshing
                return $this->refreshToken($request);
            }
        } else {
            return Services::response()
                ->setJSON(['success' => false, 'message' => 'Token Required'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    protected function refreshToken(RequestInterface $request)
    {
        // Check if the request has a refresh token
        if ($request->hasHeader('Refresh-Token')) {
            $refreshToken = $request->getHeader('Refresh-Token')->getValue();

            $jwtService = new JwtService();
            $decodedRefreshToken = $jwtService->decodeToken($refreshToken);

            if ($decodedRefreshToken) {
                // Refresh token is valid, generate a new access token
                $newAccessToken = $jwtService->generateAccessToken(get_object_vars($decodedRefreshToken->data));

                return Services::response()
                    ->setJSON(['success' => false, 'message' => 'Token Expired', 'access-token' => $newAccessToken]);
            } else {
                // Both access and refresh tokens are invalid
                return Services::response()
                    ->setJSON(['success' => false, 'message' => 'Invalid tokens'])
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            }
        } else {
            // No refresh token provided
            return Services::response()
                ->setJSON(['success' => false, 'message' => 'Access token expired, and no refresh token provided'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
