<?php
/* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	Old Guy's version of Akismet PHP4 class
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	Original PHP5 class by Alex Potsides, http://www.achingbrain.net
	Converted to PHP4 by Bret Kuhns, http://www.l33thaxor.com
	Enhanced by Richard Williamson (aka Old Guy), http://www.scripts.oldguy.us, June 2006
	
	Copyright Alex Potsides
	Licensed under the terms of the GNU General Public License, June 1991.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
	CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 

This class allows use of the Akismet (http://akismet.com) anti-comment spam service in any PHP4 application. The service performs a number of checks on submitted data and returns whether or not the data is likely to be spam. It takes the functionality from the Akismet WordPress plugin written by Matt Mullenweg (http://photomatt.net) and allows it to be integrated into any PHP4 application or website.

To use this class, you must have a vaild WordPress API key (http://wordpress.com/api-keys). They are free for non-commercial use and getting one will only take a couple of minutes. For commercial. use there are Akismet commercial licenses (http://akismet.com/commercial). 

Changes made by Old Guy:
- Replaced trigger_error() on fsockopen failure with status code that is retrieved via isKeyValid(). This allows your script to handle the error instead of having execution halt and your visitor seeing an ugly error message. Disabled PHP error display before the fsockopen to prevent the PHP error message and re-enabled error display after the error is trapped.
- Replaced trigger_error() on invalid key with status code that is returned by isCommentSpam().

The basic usage structure in your script will be something like the following:

	// Initialize and verify API key
	$akismet = new Akismet($your_site_url, $your_akismet_key]);
	$result = $akismet->isKeyValid();
	// Possible values: 'valid', 'invalid', 'no connect'
	if ($result != 'valid') {
		if (($result == 'invalid')) {
			// Invalid key
			store the comment in the moderation queue
			error email to administrator
			return
		} else {
			// Could not connect to the Akismet server
			store the comment in the moderation queue
			error email to administrator
			return
		}
	}
	// Pass comment info to the class
	$akismet->setCommentAuthorEmail($author_email);
	$akismet->setCommentAuthor($author_name);
	$akismet->setCommentAuthorURL($author_url);
	$akismet->setCommentContent($comment_text);
	$akismet->setPermalink($your_site_url . $path_to_page);
	$akismet->setCommentType('Comment');
	// Check the comment for spam
	$result = $akismet->isCommentSpam();
	// Possible values: 'false' (not spam), 'true' (spam), 'no connect'
	if ($result != 'false') {
		if ($result == 'true') {
			// The comment is spam
			mark as spam, store the comment, delete after n days 
			(you should review these in case of a mis-diagnosis)
		} else {
			// Could not connect to the Akismet server
			store the comment in the moderation queue
			error email to administrator
			return
		}
	} else {
		store the comment normally
	}

Other functions you may use are:
	$akismet->setUserIP($userip)					defaults to $_SERVER['REMOTE_ADDR'];
	$akismet->setReferrer($referrer)				defaults to $_SERVER['HTTP_REFERER'];
	$akismet->setAPIPort($apiPort)					defaults to 80
	$akismet->setAkismetServer($akismetServer)		defaults to 'rest.akismet.com'
	$akismet->setAkismetVersion($akismetVersion)	defaults to '1.1'
	$akismet->submitSpam()							no status returned
	$akismet->submitHam()							no status returned

*/
class Akismet {
	var $version = '0.3';
	var $wordPressAPIKey;
	var $blogURL;
	var $comment;
	var $apiPort;
	var $akismetServer;
	var $akismetVersion;

	// This prevents some potentially sensitive information from being sent accross the wire.
	var $ignore = array('HTTP_COOKIE',
							'HTTP_X_FORWARDED_FOR',
							'HTTP_X_FORWARDED_HOST',
							'HTTP_MAX_FORWARDS',
							'HTTP_X_FORWARDED_SERVER',
							'REDIRECT_STATUS',
							'SERVER_PORT',
							'PATH',
							'DOCUMENT_ROOT',
							'SERVER_ADMIN',
							'QUERY_STRING',
							'PHP_SELF' );

	// Initialize Akismet and verify API key
	function Akismet($blogURL, $wordPressAPIKey) {
		$this->blogURL = $blogURL;
		$this->wordPressAPIKey = $wordPressAPIKey;
		$this->apiPort = 80;
		$this->akismetServer = 'rest.akismet.com';
		$this->akismetVersion = '1.1';
		$this->comment['blog'] = $blogURL;
		$this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$this->comment['referrer'] = $_SERVER['HTTP_REFERER'];
		$this->comment['user_ip'] = $_SERVER['REMOTE_ADDR'];
		// Check to see if the key is valid
		$response = $this->http_post('key=' . $this->wordPressAPIKey . '&blog=' . $this->blogURL, $this->akismetServer, '/' . $this->akismetVersion . '/verify-key');
		if($response[1] == 'valid') {
			$this->keyStatus = 'valid';
		} elseif ($response[1] == 'no connect') {
			$this->keyStatus = 'no connect';
		} else {
			$this->keyStatus = 'invalid';
		}
	}
	
	// Use this after initializing Akismet
	function isKeyValid() {
		return $this->keyStatus;
	}

	// Checks the comment for spam
	function isCommentSpam() {
		$response = $this->http_post($this->getQueryString(), $this->wordPressAPIKey . '.rest.akismet.com', '/' . $this->akismetVersion . '/comment-check');
		return ($response[1]);
	}
	
	// Used if Akismet should have flagged a comment as spam but did not
	function submitSpam() {
		$this->http_post($this->getQueryString(), $this->wordPressAPIKey . '.' . $this->akismetServer, '/' . $this->akismetVersion . '/submit-spam');
	}

	// Used if Akismet flagged a comment as spam but it is not spam
	function submitHam() {
		$this->http_post($this->getQueryString(), $this->wordPressAPIKey . '.' . $this->akismetServer, '/' . $this->akismetVersion . '/submit-ham');
	}
	
	function http_post($request, $host, $path) {
		$http_request  = "POST " . $path . " HTTP/1.1\r\n";
		$http_request .= "Host: " . $host . "\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "User-Agent: Akismet PHP4 Class (modified by RMW) " . $this->version . " | Akismet/1.11\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$socketWriteRead = new SocketWriteRead($host, $this->apiPort, $http_request);
		$socketWriteRead->send();
		$response = explode("\r\n\r\n", $socketWriteRead->getResponse(), 2);
		$sendStatus = $socketWriteRead->getsendStatus();
		if ($sendStatus == 'no connect') {
			$response[1] = 'no connect';
		}
		return $response;
	}

	// Formats the data for transmission
	function getQueryString() {
		foreach($_SERVER as $key => $value) {
			if(!in_array($key, $this->ignore)) {
				if($key == 'REMOTE_ADDR') {
					$this->comment[$key] = $this->comment['user_ip'];
				} else {
					$this->comment[$key] = $value;
				}
			}
		}
		$query_string = '';
		foreach($this->comment as $key => $data) {
			$query_string .= $key . '=' . urlencode(stripslashes($data)) . '&';
		}
		return $query_string;
	}

	function setUserIP($userip) {
		$this->comment['user_ip'] = $userip;
	}

	function setReferrer($referrer) {
		$this->comment['referrer'] = $referrer;
	}

	function setPermalink($permalink) {
		$this->comment['permalink'] = $permalink;
	}

	function setCommentType($commentType) {
		$this->comment['comment_type'] = $commentType;
	}

	function setCommentAuthor($commentAuthor) {
		$this->comment['comment_author'] = $commentAuthor;
	}

	function setCommentAuthorEmail($authorEmail) {
		$this->comment['comment_author_email'] = $authorEmail;
	}

	function setCommentAuthorURL($authorURL) {
		$this->comment['comment_author_url'] = $authorURL;
	}

	function setCommentContent($commentBody) {
		$this->comment['comment_content'] = $commentBody;
	}

	function setAPIPort($apiPort) {
		$this->apiPort = $apiPort;
	}

	function setAkismetServer($akismetServer) {
		$this->akismetServer = $akismetServer;
	}

	function setAkismetVersion($akismetVersion) {
		$this->akismetVersion = $akismetVersion;
	}
}


// Utility class used by Akismet
class SocketWriteRead {
	var $host;
	var $port;
	var $request;
	var $response;
	var $responseLength;
	var $errorNumber;
	var $errorString;

	function SocketWriteRead($host, $port, $request, $responseLength = 1160) {
		$this->host = $host;
		$this->port = $port;
		$this->request = $request;
		$this->responseLength = $responseLength;
		$this->errorNumber = 0;
		$this->errorString = '';
	}

	// Sends the data to the remote host.
	function send() {
		$this->response = '';
		$this->sendStatus = '';
		/*ini_set('display_errors','false');*/
		$fs = fsockopen($this->host, $this->port, $this->errorNumber, $this->errorString, 3);
		if($this->errorNumber != 0) {
			$this->sendStatus = 'no connect';
		}
		/*ini_set('display_errors','true');*/
		if($fs !== false) {
			@fwrite($fs, $this->request);
			while(!feof($fs)) {
				$this->response .= fgets($fs, $this->responseLength);
			}
			fclose($fs);
		}
	}

	function getsendStatus() {
		return $this->sendStatus;
	}

	function getResponse() {
		return $this->response;
	}

	function getErrorNumner() {
		return $this->errorNumber;
	}

	function getErrorString() {
		return $this->errorString;
	}
}
?>