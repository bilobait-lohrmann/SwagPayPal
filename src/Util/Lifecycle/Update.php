<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Util\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\PayPal\Setting\Service\SettingsService;
use Swag\PayPal\SwagPayPal;
use Swag\PayPal\Webhook\WebhookService;

class Update
{
    /**
     * @var SystemConfigService
     */
    private $systemConfig;

    /**
     * @var EntityRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var WebhookService|null
     */
    private $webhookService;

    public function __construct(
        SystemConfigService $systemConfig,
        EntityRepositoryInterface $customFieldRepository,
        ?WebhookService $webhookService
    ) {
        $this->systemConfig = $systemConfig;
        $this->customFieldRepository = $customFieldRepository;
        $this->webhookService = $webhookService;
    }

    public function update(UpdateContext $updateContext): void
    {
        if (\version_compare($updateContext->getCurrentPluginVersion(), '1.1.0', '<')) {
            $this->updateTo110();
        }

        if (\version_compare($updateContext->getCurrentPluginVersion(), '1.3.0', '<')) {
            $this->updateTo130();
        }

        if (\version_compare($updateContext->getCurrentPluginVersion(), '1.7.0', '<')) {
            $this->updateTo170();
        }

        // ToDo: adjust versioning to REPLACE-GLOBAL-WITH-NEXT-VERSION
        if (\version_compare($updateContext->getCurrentPluginVersion(), '1.8.0', '<')) {
            $this->updateTo180($updateContext->getContext());
        }
    }

    private function updateTo110(): void
    {
        $this->systemConfig->set(SettingsService::SYSTEM_CONFIG_DOMAIN . 'installmentBannerEnabled', true);
    }

    private function updateTo130(): void
    {
        if (!$this->systemConfig->get(SettingsService::SYSTEM_CONFIG_DOMAIN . 'sandbox')) {
            return;
        }

        $previousClientId = $this->systemConfig->get(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientId');
        $previousClientSecret = $this->systemConfig->get(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientSecret');
        $previousClientIdSandbox = $this->systemConfig->get(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientIdSandbox');
        $previousClientSecretSandbox = $this->systemConfig->get(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientSecretSandbox');

        if ($previousClientId && $previousClientSecret
            && $previousClientIdSandbox === null && $previousClientSecretSandbox === null) {
            $this->systemConfig->set(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientIdSandbox', $previousClientId);
            $this->systemConfig->set(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientSecretSandbox', $previousClientSecret);
            $this->systemConfig->set(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientId', '');
            $this->systemConfig->set(SettingsService::SYSTEM_CONFIG_DOMAIN . 'clientSecret', '');
        }
    }

    private function updateTo170(): void
    {
        if ($this->webhookService === null) {
            // If the WebhookService is `null`, the plugin is deactivated.
            return;
        }

        $this->webhookService->registerWebhook(null);
    }

    private function updateTo180(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', SwagPayPal::ORDER_TRANSACTION_CUSTOM_FIELDS_PAYPAL_TRANSACTION_ID));

        $customFieldIds = $this->customFieldRepository->searchIds($criteria, $context);

        if ($customFieldIds->getTotal() === 0) {
            return;
        }

        $data = \array_map(static function ($id) {
            return ['id' => $id];
        }, $customFieldIds->getIds());
        $this->customFieldRepository->delete($data, $context);
    }
}
