<?php

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/ratepay/profile-configuration", defaults={"_routeScope"={"administration"}})
 */
class ProfileConfigController extends AbstractController
{
    private ProfileConfigManagement $profileManagement;

    public function __construct(ProfileConfigManagement $profileManagement)
    {
        $this->profileManagement = $profileManagement;
    }

    /**
     * @Route("/reload-config/", name="ratepay.profile.config.reload", methods={"POST"})
     */
    public function reloadProfileConfiguration(Request $request): JsonResponse
    {
        if ($id = $request->request->get('id')) {
            try {
                $configs = $this->profileManagement->refreshProfileConfigs([$id]);
                $response = [
                    'error' => [],
                    'success' => [],
                ];
                /** @var ProfileConfigEntity $config */
                foreach ($configs->getEntities() as $config) {
                    $response[$config->getStatus() ? 'success' : 'error'][$config->getProfileId()] = $config->getStatusMessage();
                }

                return $this->json($response, 200);
            } catch (Exception $exception) {
                return $this->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            }
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Invalid profile-id',
            ], 400);
        }
    }
}
