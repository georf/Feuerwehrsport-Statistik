<?php

class CountTable {
  public static function get($type, $entries, $id = 'id') {
    return CountTable::build($entries)
      ->col(Title::name($type), function ($entry) use ($type, $id) {
          if (isset($entry['name'])) {
            return call_user_func('Link::'.$type, $entry[$id], $entry['name']);
          } else {
            return call_user_func('Link::'.$type, $entry[$id]);
          }
        }, 80)
      ->col("Wettkämpfe", 'count', 20);
  }

  public static function build($rows, $classes = array()) {
    return new self($rows, $classes);
  }

  private $cols = array();
  private $rows = array();
  private $classes = array();
  private $rowAttributes = array();

  public function __construct($rows, $classes) {
    $this->rows = $rows;
    $this->classes = $classes;
    $this->classes[] = "datatable";
  }

  public function addClass($class) {
    $this->classes[] = $class;
    return $this;
  }

  public function rowAttribute($name, $value) {
    $this->rowAttributes[] = array($name, $value);
    return $this;
  }

  public function col($title, $content, $width = false, $attributes = array(), $attributesTh = array()) {
    $this->cols[] = new CountTableCol($title, $content, $width, $attributes, $attributesTh);
    return $this;
  }

  public function __toString() {
    $width = 0;
    foreach ($this->cols as $col) $width += $col->width();
    foreach ($this->cols as $col) $col->setFullWidth($width);

    $output = '<table class="'.implode(" ", $this->classes).'">';
    $output .= '<thead><tr>';
    $output .= implode($this->cols);
    $output .= '</tr></thead>';
    $output .= '<tbody>';

    foreach ($this->rows as $key => $row) {
      $output .= '<tr';

      foreach ($this->rowAttributes as $attribute) {
        list($name, $value) = $attribute;
        $output .= ' '.htmlspecialchars($name).'="';
        $output .= ($value instanceof Closure)? $value($row, $key) : htmlspecialchars($row[$value]);
        $output .= '"';
      }
      $output .= '>';

      foreach ($this->cols as $col) {
        $output .= $col->td($row, $key);
      }
      $output .= '</tr>';
    }
    $output .= '</tbody></table>';
    return $output;
  }
}

class CountTableCol {
  private $title;
  private $width;
  private $content;
  private $attributes;
  private $attributesTh;
  private $fullWidth;
  public function __construct($title, $content, $width, $attributes, $attributesTh) {
    $this->title = $title;
    $this->width = $width;
    $this->content = $content;
    $this->attributes = $attributes;
    $this->attributesTh = $attributesTh;
  }
  public function __toString() {
    $output = '<th';
    if (is_numeric($this->width)) $output .= ' style="width:'.round($this->width/$this->fullWidth*100).'%"';
    foreach ($this->attributesTh as $key => $value) {
      $output .= ' '.$key.'="';
      $output .= ($value instanceof Closure)? $value($row) : $value;
      $output .= '"';
    }
    $output .= '>'.$this->title.'</th>';
    return $output;
  }
  public function td($row, $rowKey) {
    $output = '<td';
    foreach ($this->attributes as $key => $value) {
      $output .= ' '.$key.'="';
      $output .= ($value instanceof Closure)? $value($row) : $value;
      $output .= '"';
    }
    $output .= '>';
    $c = $this->content;
    $output .= ($c instanceof Closure)? $c($row, $rowKey) : htmlspecialchars($row[$c]);
    $output .= '</th>';
    return $output;
  }
  public function width() {
    return is_numeric($this->width)? $this->width : 50;
  }
  public function setFullWidth($width) {
    $this->fullWidth = $width;
  }
}
