<?php

namespace Persysiphus;

function dataDir($value = false) {
	static $dataDir = '/data/';
	if($value) { 
		$dataDir = $value;
	}
	return $dataDir; 
}

class Record {
	protected $name; 
	protected $data; 
	
	public function __construct($name) {
		$this->name = $name;
		
		if(!file_exists($this->fileName())) {
			$this->data = (object)array();
		} else {
			$this->data = json_decode(file_get_contents($this->fileName()));
		}
	}

	public function __set($key, $val) {
		$this->data->$key = $val;
		return $this;
	}
	
	public function set($key, $val) {
		return $this->__set($key, $val);
	}
	
	public function __get($key) {
		if(!isset($this->data->$key)) {
			throw new \Exception("$key not found in {$this->name}");
		}
		return $this->data->$key;
	}
	
	public function save() { 
		file_put_contents($this->fileName(), json_encode($this->data));
		return $this;
	}
	
	protected function fileName() {
		return dataDir() . $this->name;
	}
	
	public function toArray() {
		return $this->data;
	}
}

class Collection {
	protected $name;
	protected $info; 
	protected $records; 
	
	public function __construct($name, $record = false) {
		$this->name = $name;
		$this->info = new Record("$name.info");
		
		if(!file_exists($this->fileName())) {
			touch($this->fileName());
			$this->info->id = 1;
			$this->info->save();
		}
		
		if(!empty($record) && is_object($record) || is_array($record)) {
			$this->create($record);
		}
	}

	public function __set($key, $val) {
		$this->info->$key = $val;
		$this->info->save();
	}
	
	public function __get($key) {
		if(!isset($this->info)) {
			throw new \Exception("No property $key");
		}
		return $this->info->$key;
	}
	
	public function saveInfo($info) { 
		foreach($info as $key => $val) { 
			$this->info->$key = $val;
		}
		$this->info->save();
		return $this;
	}
	
	public function getInfo() {
		return $this->info;
	}
	
	public function getName() {
		return $this->name;
	}
	
	protected function fileName() {
		return dataDir() . $this->name;
	}

	public function now() {
		return date('m/d/y H:i:s');
	}
	
	public function filterThenSave($func) {
		file_put_contents(
			$this->getFileName(), 
			implode(
				",\n", 
				array_map(
					'json_encode',
					$this->filter($func))));

		return $this;
	}
	
	public function delete($data) {
		$file = $this->getFileName();
		return $this->filterThenSave(function($item) use($data, $file) { 
			if($item->id == $data['id']) {
				Collection($file . '.deleted')->create($data);
				return false;
			}
			return true;
		});
	}
	
	public function update($data) {
		$file = '';
		foreach($this->getAll() as $record) { 
			if($record->id == $data->id) {
				$record = $data;
			}
			$file .= json_encode($record) . ",\n";
		}
		
		file_put_contents($this->fileName(), $file);
		return $data;
	}
	
	public function create($data) {
		$data = (object)$data;
		$data->id = $this->info->id++;
		$this->info->save();
		$data->creationDate = $this->now();
		file_put_contents($this->fileName(), json_encode($data) . ",\n", FILE_APPEND);
		return $data;
	}
		
	public function	getAll() {
		if(!$this->records) {
			$this->records = json_decode('[' . substr(file_get_contents($this->fileName()), 0, -2) .']');	
		}
		return $this->records;
	}
	
	public function filter($func) {
		return array_filter($this->getAll(), $func);
	}
	
	public function get($id) {
		return $this->getFirstBy('id', $id);
	}
	
	public function getBy($field, $value) {
		return $this->filter(function($record) use($field, $value) {
			return $record->$field == $value;
		});
	}

	public function getFirstBy($field, $value) {
		$records = $this->getBy($field, $value);
		return empty($records) ? false : reset($records);
	}

	public function changeOrder($after, $data) {
		$order = 0;
		
		$data = (object)$data;
		
		$file = '';
		if(!$after) {
			$data->orderBy = $order++;
			$file .= json_encode($data) . ",\n";	
		}

		foreach($this->getAll() as $item) {
			if($item->id == $data->id) {
				continue;
			}

			$item->orderBy = $order++;
			$file .= json_encode($item) . ",\n";

			if($item->id == $after) {
				$item->orderBy = $order++;
				$file .= json_encode($item) . ",\n";
			}
		}
		
		file_put_contents($filename, $file);	
	}
	
	public static function __callStatic($what, $with) {
		return new Collection($what, !empty($with) ? current($with) : false);
	}
	
	public static function doesExist($uqn) {
		return file_exists(dataDir() . $uqn);
	}
}

function Collection($name) {
	return new Collection($name);
}