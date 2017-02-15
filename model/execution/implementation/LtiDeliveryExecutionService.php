<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\ltiDeliveryProvider\model\execution\implementation;

use oat\ltiDeliveryProvider\model\execution\LtiDeliveryExecutionService as LtiDeliveryExecutionServiceInterface;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\oatbox\service\ConfigurableService;

/**
 * Class LtiDeliveryExecutionService
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\ltiDeliveryProvider\model\execution
 */
class LtiDeliveryExecutionService extends ConfigurableService implements LtiDeliveryExecutionServiceInterface
{
    /**
     * @inheritdoc
     */
    public function isFinished(DeliveryExecution $deliveryExecution)
    {
        return $deliveryExecution->getState()->getUri() === DeliveryExecution::STATE_FINISHIED;
    }
}