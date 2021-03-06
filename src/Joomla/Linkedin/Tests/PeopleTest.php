<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Linkedin\Tests;

use Joomla\Linkedin\People;
use \DomainException;
use \stdClass;

require_once __DIR__ . '/case/LinkedinTestCase.php';

/**
 * Test class for People.
 *
 * @since  1.0
 */
class PeopleTest extends LinkedinTestCase
{
	/**
	 * @var    string  Sample JSON string used to access out of network profiles.
	 * @since  1.0
	 */
	protected $outString = '{"headers": { "_total": 1, "values": [{ "name": "x-li-auth-token",
				"value": "NAME_SEARCH:-Ogn" }] }, "url": "/v1/people/oAFz-3CZyv"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new People($this->options, $this->client, $this->oauth);
	}

	/**
	 * Provides test data for request format detection.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function seedIdUrl()
	{
		// Member ID or url
		return array(
			array('lcnIwDU0S6', null),
			array(null, 'http://www.linkedin.com/in/dianaprajescu'),
			array(null, null)
			);
	}

	/**
	 * Tests the getProfile method
	 *
	 * @param   string  $id   Member id of the profile you want.
	 * @param   string  $url  The public profile URL.
	 *
	 * @return  void
	 *
	 * @dataProvider seedIdUrl
	 * @since   1.0
	 */
	public function testGetProfile($id, $url)
	{
		$fields = '(id,first-name,last-name)';
		$language = 'en-US';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/';

		if ($url)
		{
			$path .= 'url=' . $this->oauth->safeEncode($url) . ':public';
			$type = 'public';
		}
		else
		{
			$type = 'standard';
		}

		if ($id)
		{
			$path .= 'id=' . $id;
		}
		elseif (!$url)
		{
			$path .= '~';
		}

		$path .= ':' . $fields;
		$header = array('Accept-Language' => $language);

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get', $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getProfile($id, $url, $fields, $type, $language),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getProfile method - failure
	 *
	 * @param   string  $id   Member id of the profile you want.
	 * @param   string  $url  The public profile URL.
	 *
	 * @return  void
	 *
	 * @dataProvider seedIdUrl
	 * @since   1.0
	 * @expectedException DomainException
	 */
	public function testGetProfileFailure($id, $url)
	{
		$fields = '(id,first-name,last-name)';
		$language = 'en-US';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/';

		if ($url)
		{
			$path .= 'url=' . $this->oauth->safeEncode($url) . ':public';
			$type = 'public';
		}
		else
		{
			$type = 'standard';
		}

		if ($id)
		{
			$path .= 'id=' . $id;
		}
		elseif (!$url)
		{
			$path .= '~';
		}

		$path .= ':' . $fields;
		$header = array('Accept-Language' => $language);

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get', $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getProfile($id, $url, $fields, $type, $language);
	}

	/**
	 * Tests the getConnections method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetConnections()
	{
		$fields = '(id,first-name,last-name)';
		$start = 1;
		$count = 50;
		$modified = 'new';
		$modified_since = '1267401600000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['modified'] = $modified;
		$data['modified-since'] = $modified_since;

		$path = '/v1/people/~/connections';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getConnections($fields, $start, $count, $modified, $modified_since),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getConnections method - failure
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @expectedException DomainException
	 */
	public function testGetConnectionsFailure()
	{
		$fields = '(id,first-name,last-name)';
		$start = 1;
		$count = 50;
		$modified = 'new';
		$modified_since = '1267401600000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['modified'] = $modified;
		$data['modified-since'] = $modified_since;

		$path = '/v1/people/~/connections';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getConnections($fields, $start, $count, $modified, $modified_since);
	}

	/**
	 * Provides test data for request format detection.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function seedFields()
	{
		// Fields
		return array(
			array('(people:(id,first-name,last-name,api-standard-profile-request))'),
			array('(people:(id,first-name,last-name))')
			);
	}

	/**
	 * Tests the search method
	 *
	 * @param   string  $fields  Request fields beyond the default ones. provide 'api-standard-profile-request' field for out of network profiles.
	 *
	 * @return  void
	 *
	 * @dataProvider seedFields
	 * @since   1.0
	 */
	public function testSearch($fields)
	{
		$keywords = 'Princess';
		$first_name = 'Clair';
		$last_name = 'Standish';
		$company_name = 'Smth';
		$current_company = true;
		$title = 'developer';
		$current_title = true;
		$school_name = 'Shermer High School';
		$current_school = true;
		$country_code = 'us';
		$postal_code = 12345;
		$distance = 500;
		$facets = 'location,industry,network,language,current-company,past-company,school';
		$facet = array('us-84', 47, 'F', 'en', 1006, 1028, 2345);
		$start = 1;
		$count = 50;
		$sort = 'distance';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['first-name'] = $first_name;
		$data['last-name'] = $last_name;
		$data['company-name'] = $company_name;
		$data['current-company'] = $current_company;
		$data['title'] = $title;
		$data['current-title'] = $current_title;
		$data['school-name'] = $school_name;
		$data['current-school'] = $current_school;
		$data['country-code'] = $country_code;
		$data['postal-code'] = $postal_code;
		$data['distance'] = $distance;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'location,' . $facet[0];
		$data['facet'][] = 'industry,' . $facet[1];
		$data['facet'][] = 'network,' . $facet[2];
		$data['facet'][] = 'language,' . $facet[3];
		$data['facet'][] = 'current-company,' . $facet[4];
		$data['facet'][] = 'past-company,' . $facet[5];
		$data['facet'][] = 'school,' . $facet[6];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/people-search';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		if (strpos($fields, 'api-standard-profile-request') === false)
		{
			$this->client->expects($this->once())
				->method('get')
				->with($path)
				->will($this->returnValue($returnData));
		}
		else
		{
			$returnData = new stdClass;
			$returnData->code = 200;
			$returnData->body = $this->outString;

			$this->client->expects($this->at(0))
				->method('get')
				->with($path)
				->will($this->returnValue($returnData));

			$returnData = new stdClass;
			$returnData->code = 200;
			$returnData->body = $this->sampleString;

			$path = '/v1/people/oAFz-3CZyv';
			$path = $this->oauth->toUrl($path, $data);

			$name = 'x-li-auth-token';
			$value = 'NAME_SEARCH:-Ogn';
			$header[$name] = $value;

			$this->client->expects($this->at(1))
				->method('get', $header)
				->with($path)
				->will($this->returnValue($returnData));
		}

		$this->assertThat(
			$this->object->search(
				$fields, $keywords, $first_name, $last_name, $company_name,
				$current_company, $title, $current_title, $school_name, $current_school, $country_code,
				$postal_code, $distance, $facets, $facet, $start, $count, $sort
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the search method - failure
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @expectedException DomainException
	 */
	public function testSearchFailure()
	{
		$fields = '(id,first-name,last-name)';
		$keywords = 'Princess';
		$first_name = 'Clair';
		$last_name = 'Standish';
		$company_name = 'Smth';
		$current_company = true;
		$title = 'developer';
		$current_title = true;
		$school_name = 'Shermer High School';
		$current_school = true;
		$country_code = 'us';
		$postal_code = 12345;
		$distance = 500;
		$facets = 'location,industry,network,language,current-company,past-company,school';
		$facet = array('us-84', 47, 'F', 'en', 1006, 1028, 2345);
		$start = 1;
		$count = 50;
		$sort = 'distance';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['first-name'] = $first_name;
		$data['last-name'] = $last_name;
		$data['company-name'] = $company_name;
		$data['current-company'] = $current_company;
		$data['title'] = $title;
		$data['current-title'] = $current_title;
		$data['school-name'] = $school_name;
		$data['current-school'] = $current_school;
		$data['country-code'] = $country_code;
		$data['postal-code'] = $postal_code;
		$data['distance'] = $distance;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'location,' . $facet[0];
		$data['facet'][] = 'industry,' . $facet[1];
		$data['facet'][] = 'network,' . $facet[2];
		$data['facet'][] = 'language,' . $facet[3];
		$data['facet'][] = 'current-company,' . $facet[4];
		$data['facet'][] = 'past-company,' . $facet[5];
		$data['facet'][] = 'school,' . $facet[6];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/people-search';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->search(
			$fields, $keywords, $first_name, $last_name, $company_name,
			$current_company, $title, $current_title, $school_name, $current_school, $country_code,
			$postal_code, $distance, $facets, $facet, $start, $count, $sort
			);
	}
}
