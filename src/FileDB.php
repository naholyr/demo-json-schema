<?php

class FileDB
{

  private $filename;

  private $data;

  public function __construct ($file) {
    $this->filename = $file;
    $this->fetch();
  }

  public function all () {
    $data = array();
    foreach ($this->data['rows'] as $id => $v) {
      $data[] = array_merge($v, array('id' => substr($id, 1)));
    }
    return $data;
  }

  public function get ($id) {
    return $this->data['rows']['$' . $id];
  }

  public function set ($id, $v) {
    $this->data['rows']['$' . $id] = $v;
  }

  public function add ($v) {
    $id = $this->data['sequence']++;
    $this->data['rows']['$' . $id] = $v;
    return $id;
  }

  public function remove ($id) {
    unset($this->data['rows']['$' . $id]);
  }

  public function clear () {
    $this->data['sequence'] = 1;
    $this->data['rows'] = array();
  }

  public function fetch () {
    if (!is_file($this->filename)) {
      $this->data = array();
      $this->clear();
    } else {
      $this->data = require $this->filename;
    }
  }

  public function persist () {
    file_put_contents($this->filename, '<' . '?php return ' . var_export($this->data, true) . ';');
  }

}
