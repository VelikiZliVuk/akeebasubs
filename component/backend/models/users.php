<?php
/**
 * @package		akeebasubs
 * @copyright	Copyright (c)2010-2011 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

class ComAkeebasubsModelUsers extends KModelTable
{
	public function __construct(KConfig $config)
	{
		parent::__construct($config);

		$this->_state
			->insert('ordering'	, 'int')
			->insert('enabled'	, 'int')
			// The user_id column is part of a unique index, causing invalid SQL to be output
			// when only searching by user_id. Bummer. I fscked up the data modelling on that
			// table :(
			->remove('user_id')
			->insert('user_id'	, 'int', null, false)
			;
	}

	protected function _buildQueryWhere(KDatabaseQuery $query)
	{
		$state = $this->_state;

		if(is_numeric($state->ordering)) {
			$query->where('tbl.ordering','=', $state->ordering);
		}
		
		if(is_numeric($state->enabled)) {
			$query->where('tbl.enabled','=', $state->enabled);
		}
		
		if(is_numeric($state->user_id) && ($state->user_id > 0)) {
			$query->where('tbl.user_id','=',$state->user_id);
		}
		
		if($state->search)
		{
			$search = '%'.$state->search.'%';
			$query->where('businessname', 'LIKE',  $search, 'OR');
			$query->where('occupation', 'LIKE',  $search, 'OR');
			$query->where('vatnumber', 'LIKE',  $search, 'OR');
			$query->where('address1', 'LIKE',  $search, 'OR');
			$query->where('address2', 'LIKE',  $search, 'OR');
			$query->where('city', 'LIKE',  $search, 'OR');
			$query->where('state', 'LIKE',  $search, 'OR');
			$query->where('zip', 'LIKE',  $search, 'OR');
		}
		
		parent::_buildQueryWhere($query);
	}
	
	public function getMergedData()
	{
		// Get a legacy data set from the user parameters
		$userRow = KFactory::tmp('admin::com.akeebasubs.model.jusers')->id($this->_state->user_id)->getItem();
		$params = new JParameter($userRow->params);
		$businessname = $params->get('business_name','');
		$nativeData = array(
			'isbusiness' => empty($businessname) ? 0 : 1,
			'businessname' => $params->get('business_name',''),
			'occupation' => $params->get('occupation',''),
			'vatnumber' => $params->get('vat_number',''),
			'viesregistered' => 0,
			'taxauthority' => '',
			'address1' => $params->get('address',''),
			'address2' => $params->get('address2',''),
			'city' => $params->get('city',''),
			'state' => $params->get('state',''),
			'zip' => $params->get('zip',''),
			'country' => $params->get('country',''),
		);
		$nativeData = array_merge($nativeData, $userRow->getData());
		
		$myData = $this->getItem()->getData();
		if($this->getItem()->id > 0) {		
			$myData = array_merge($nativeData, $myData);
		} else {
			$myData = $nativeData;
		}
		
		return (object)$myData;
	}
}