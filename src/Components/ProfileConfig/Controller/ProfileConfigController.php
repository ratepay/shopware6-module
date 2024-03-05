<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Controller;

use Exception;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/_action/ratepay/profile-configuration', defaults: [
    '_routeScope' => ['administration'],
])]
class ProfileConfigController extends AbstractController
{
    public function __construct(
        private readonly ProfileConfigManagement $profileManagement
    ) {
    }

    #[Route(path: '/reload-config', name: 'ratepay.profile.config.reload', methods: ['POST'])]
    public function reloadProfileConfiguration(Request $request): Response
    {
        if ($id = $request->request->get('id')) {
            try {
                $configs = $this->profileManagement->refreshProfileConfigs([$id]);
                $profileConfig = $configs->get($id);
                if (!$profileConfig instanceof ProfileConfigEntity) {
                    return $this->json([
                        'success' => false,
                        'message' => 'profile does not exist',
                    ], 404);
                }

                if (!$profileConfig->getStatus()) {
                    return $this->json([
                        'success' => false,
                        'message' => $profileConfig->getStatusMessage() ?? 'Unknown error.',
                    ], 500);
                }

                return new Response(null, Response::HTTP_NO_CONTENT);
            } catch (Exception $exception) {
                return $this->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            }
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Missing parameter `id`',
            ], 400);
        }
    }
}
