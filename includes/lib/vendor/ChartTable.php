<?php

class ChartTable {
  public static function build($classes = array()) {
    return new self($classes);
  }

  private $rows = array();
  private $classes = array();

  public function __construct($classes) {
    $this->classes = $classes;
    $this->classes[] = "chart-table";
  }

  public function row($title, $content = false, $htmlTitle = false) {
    $this->rows[] = array($title, $content, $htmlTitle);
    return $this;
  }

  public function __toString() {
    $output = '<table class="'.implode(" ", $this->classes).'">';
    foreach ($this->rows as $row) {
      $output .= '<tr';
      if ($row[2] === false) $output .= ' title="'.$row[2].'"';
      $output .= '><th';
      if ($row[1] === false) $output .= ' colspan="2"';
      $output .= '>'.$row[0].'</th>';
      if ($row[1] !== false) $output .= '<td>'.$row[1].'</td>';
      $output .= '</tr>';
    }
    $output .= '</table>';
    return $output;
  }
}