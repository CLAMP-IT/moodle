<?php
/*******
 * SOAP service class for FunctionalCapability services
 * @author jacob
 *
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 **/
class TurningTechFunctionalCapabilityService extends TurningTechSoapService {
    public function TurningTechFunctionalCapabilityService() {
        parent::TurningTechSoapService();
    }

    /**
     * get list of capabilities for user
     * @param $request
     * @return array of functionalCapabilityDto
     */
    public function getFunctionalCapabilities($request) {
        $user         = NULL;
        $capabilities = NULL;

        $user = $this->authenticateRequest($request);
        return $this->service->getUserCapabilities($user);
    }
}
?>