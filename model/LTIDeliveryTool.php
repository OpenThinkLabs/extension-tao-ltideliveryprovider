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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

namespace oat\ltiDeliveryProvider\model;

use oat\ltiDeliveryProvider\helper\ResultServer;
use \taoLti_models_classes_LtiTool;
use \taoLti_models_classes_LtiService;
use \core_kernel_classes_Property;
use \core_kernel_classes_Resource;
use \core_kernel_classes_Class;
use \common_session_SessionManager;
use \taoDelivery_models_classes_execution_ServiceProxy;

class LTIDeliveryTool extends taoLti_models_classes_LtiTool {
	
	const TOOL_INSTANCE = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIToolDelivery';
	
    const EXTENSION = 'ltiDeliveryProvider';
	const MODULE = 'DeliveryTool';
	const ACTION = 'launch';
	
	public function getLaunchUrl($parameters = array()) {
		$fullAction = self::ACTION.'/'.base64_encode(json_encode($parameters));
		return _url($fullAction, self::MODULE, self::EXTENSION);
	}
	
	public function getDeliveryFromLink() {
		$remoteLink = taoLti_models_classes_LtiService::singleton()->getLtiSession()->getLtiLinkResource();
		return $remoteLink->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_LINK_DELIVERY));
	}
	
	public function linkDeliveryExecution(core_kernel_classes_Resource $link, $userUri, core_kernel_classes_Resource $deliveryExecution) {
	    $class = new core_kernel_classes_Class(CLASS_LTI_DELIVERYEXECUTION_LINK);
	    $link = $class->createInstanceWithProperties(array(
	        PROPERTY_LTI_DEL_EXEC_LINK_USER => $userUri,
	        PROPERTY_LTI_DEL_EXEC_LINK_LINK => $link,
            PROPERTY_LTI_DEL_EXEC_LINK_DELIVERYEXEC => $deliveryExecution
	    ));
	    return $link instanceof core_kernel_classes_Resource;
	}
	
	protected function getLinkedDeliveryExecution(core_kernel_classes_Resource $link, $userUri) {
	    
	    $returnValue = null;
	    
	    $class = new core_kernel_classes_Class(CLASS_LTI_DELIVERYEXECUTION_LINK);
	    $candidates = $class->searchInstances(array(
	    	PROPERTY_LTI_DEL_EXEC_LINK_USER => $userUri,
	        PROPERTY_LTI_DEL_EXEC_LINK_LINK => $link,
	    ), array(
	    	'like' => false
	    ));
	    foreach ($candidates as $link) {
	        $deliveryExecution = $link->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_LTI_DEL_EXEC_LINK_DELIVERYEXEC));
	        $status = $deliveryExecution->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_DELVIERYEXECUTION_STATUS));
	        if ($status->getUri() == INSTANCE_DELIVERYEXEC_ACTIVE) {
	           $returnValue = $deliveryExecution;
	           break;
	        }
	    }
	    return $returnValue;
	}
	
	/**
	 * Starts or resumes a compiled Delivery and returns
	 * the active Delivery Execution
	 * 
	 * @param core_kernel_classes_Resource $compiledDelivery
	 * @return core_kernel_classes_Resource
	 */
	public function startResumeDelivery(core_kernel_classes_Resource $delivery) {
	    
	    $remoteLink = taoLti_models_classes_LtiService::singleton()->getLtiSession()->getLtiLinkResource();
	    $userId = common_session_SessionManager::getSession()->getUserUri();
	    $deliveryExecution = $this->getLinkedDeliveryExecution($remoteLink, $userId);
	    if (is_null($deliveryExecution)) {
	        $deliveryExecution = taoDelivery_models_classes_execution_ServiceProxy::singleton()->initDeliveryExecution(
	            $delivery,
	            $userId
	        );
	        $this->linkDeliveryExecution($remoteLink, $userId, $deliveryExecution);
	    }

	    //The result server from LTI context depend on call parameters rather than static result server definition
	    $launchData = taoLti_models_classes_LtiService::singleton()->getLtiSession()->getLaunchData();
        ResultServer::initLtiResultServer($delivery, $deliveryExecution, $launchData);
	    // lis_outcome_service_url This value should not change from one launch to the next and in general,
        //  the TP can expect that there is a one-to-one mapping between the lis_outcome_service_url and a particular oauth_consumer_key.  This value might change if there was a significant re-configuration of the TC system or if the TC moved from one domain to another.
        return $deliveryExecution;
        
	}	
}