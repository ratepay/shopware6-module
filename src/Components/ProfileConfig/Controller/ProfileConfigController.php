<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\ProfileConfig\Controller;

use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RatepayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v{version}/ratepay/profile-configuration")
 */
class ProfileConfigController extends AbstractController
{
    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(ProfileConfigService $profileConfigService)
    {
        $this->profileConfigService = $profileConfigService;
    }

    /**
     * @RouteScope(scopes={"administration"})
     * @Route("/reload-config/", name="ratepay.profile.config.reload", methods={"POST"})
     * @return JsonResponse
     */
    public function reloadProfileConfiguration(Request $request)
    {
        if ($id = $request->request->get('id')) {
            try {
                $configs = $this->profileConfigService->refreshProfileConfigs([$id]);
                $response = [
                    'error' => [],
                    'success' => [],
                ];
                /** @var ProfileConfigEntity $config */
                foreach($configs->getEntities() as $config) {
                    $response[$config->getStatus() ? 'success' : 'error'][$config->getProfileId()] = $config->getStatusMessage();
                }
                return $this->json($response, 200);
            } catch (\Exception $e) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Invalid profile-id'
            ], 400);
        }
    }
}
