<?php
/**
 * Created by Bas van KlLaarbergen
 * Date: 8/20/12
 */

class Populi{
	//Set to the correct URL for your college
	protected $api_url = 'https://pbc.populiweb.com/api/index.php';

	//You can set this to a valid access token - if null, you'll need to call login() before calling doTask()
	private $api_token = null;
    private $personId = null;
    private $personEmail = null;

	public function login( $user_name, $password ){
		$params = 'username=' . urlencode($user_name) . '&password=' . urlencode($password);

		// Place the results into an XML string. We can't use file_get_contents since it randomly fails... so we now use curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $this->api_url);
		$response = curl_exec($curl);
		curl_close($curl);

		if( $response !== false) {
			// Use SimpleXML to put results into Simple XML object (requires PHP5)
			$xml = new SimpleXMLElement($response);

			if( isset($xml->access_key) ){
				$this->api_token = (string)$xml->access_key;
                $this->personId = (string)$xml->accountid;
                return $this->api_token;
			}
			else{
				throw new PopuliException("Please enter a valid username/password.", 'AUTHENTICATION_ERROR');
			}
		}
		else{
			throw new PopuliException("Oops! We're having trouble connecting to Populi right now... please try again later.", 'CONNECTION_ERROR');
		}
	}

    public function getPersonId(){
        return $this->personId;
    }

	public function getCurrentTerm($token){
        return $this->doTask($token, "getCurrentAcademicTerm");
    }

	public function getMyCourses($token, $term_id, $personId){
        $param['person_id'] = $personId;
        $param['term_id'] = $term_id;
        return $this->doTask($token, "getMyCourses", $param, false);
    }

    
    public function getAcademicTerms() {
        return $this->doTask("getAcademicTerms");
    }

    public function getPerson($token, $personId) {
        $param['person_id'] = $personId;
        return $this->doTask($token, "getPerson", $param);
    }

    public function getTermCourseInstances($termId) {
        $param['term_id'] = $termId;
        return $this->doTask("getTermCourseInstances", $param);
    }

	public function getCourseInstance($token, $instanceId) {
        $param['instance_id'] = $instanceId;
        return $this->doTask($token, "getCourseInstance", $param);
    }

    public function getCourseInstanceMeetings($token, $instanceId) {
        $param['instanceID'] = $instanceId;
        return $this->doTask($token, "getCourseInstanceMeetings", $param);
    }

	public function getCourseInstanceMeetingAttendance($token, $instanceId, $meetingId) {
        $param['instanceID'] = $instanceId;
        $param['meetingID'] = $meetingId;
        return $this->doTask($token, "getCourseInstanceMeetingAttendance", $param);
    }

    public function getCourseCatalog(){
       return $this->doTask("getCourseCatalog");
    }

    public function getCourseInstanceStudents($instanceId) {
        $param['instance_id'] = $instanceId;
        return $this->doTask("getCourseInstanceStudents", $param);
    }

    public function updateAttendance($instanceId, $studentId, $meetingId, $status){
        $params = array();
        $params['instanceID'] = $instanceId;
        $params['personID'] = $studentId;
        $params['meetingID'] = $meetingId;
        $params['status'] = $status;

        $this->doTask("updateStudentAttendance", $params);
        //$post = 'task=' . urlencode("updateStudentAttendance") . '&access_key=' . $this->api_token;
        //$post .= '&instanceID=' . urlencode($instanceId) . '&studentID=' . urlencode($studentId) . '&meetingID=' . urlencode($meetingId) . '&status=' . urlencode($status);
        
    }

	private function doTask($token, $task, $params = array(), $returnArray = false) {
		if( !$token ){
			throw new Exception("Please call login before trying to perform a task!");
		}

		$post = 'task=' . urlencode($task) . '&access_key=' . $token;

		foreach($params as $param => $value){
			if( is_array($value) ){
				foreach($value as $array_value){
					$post .= '&' . $param . '[]=' . urlencode($array_value);
				}
			}
			else{
				$post .= "&$param=" . urlencode($value);
			}
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $this->api_url);
		$response = curl_exec($curl);
		curl_close($curl);

		if( $curl !== false ){
			// Use Simple XML to put results into Simple XML object (requires PHP5)
			try{
				$xml = new SimpleXMLElement($response);
			}
			catch(Exception $e){
				echo htmlentities($response) . '<br><br><br>';
				throw new PopuliException('Problem parsing the XML response: ' . $e->getMessage());
			}

			if( $xml->getName() == 'response' ){
				if($returnArray) {
					$jsonArray = array($xml);
					return  json_encode($jsonArray);
				} else {
					return json_encode($xml);
				}
			}
			else if( $xml->getName() == 'error' ){
				throw new PopuliException((string)$xml->message, (string)$xml->code);
			}
			else{
				//Woah - response or error should always be the root element
				throw new PopuliException('Problem parsing the XML response: invalid root element.');
			}
		}
		else{
			throw new PopuliException('Could not connect to Populi.', 'CONNECTION_ERROR');
		}
	}
}

class PopuliException extends Exception{
	/**************************************************************************************************
	 *	We have our own variable since we don't feel like using numeric error codes
	 *	Should be one of:
	 *		AUTHENTICATION_ERROR - Couldn't login to the API (bad username/password)
	 *		BAD_PARAMETER - You called a task using parameters it didn't like
	 *		CONNECTION_ERROR - Thrown if we can't connect to Populi
	 *		LOCKED_OUT - Your user account is blocked (too many failed login attempts?)
	 *		OTHER_ERROR - Default generic error
	 *		PERMISSIONS_ERROR - You aren't allowed to call that task with those parameters
	 *		UKNOWN_TASK - You tried to call an API task that doesn't exist
	****************************************************************************************************/
	public $populi_code = null;

	public function __construct($message, $populi_code = 'OTHER_ERROR'){
		parent::__construct($message);
		$this->populi_code = $populi_code;
	}

	public function getPopuliCode(){
		return $this->populi_code;
	}
}

?>
