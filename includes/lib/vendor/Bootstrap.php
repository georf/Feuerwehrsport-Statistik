<?php

class Bootstrap {
  private static $ids = array();
  public static function row($classes = "") {
    return new BootstrapRow($classes);
  }
  public static function navTab($classes = '') {
    return new BootstrapNavTab($classes);
  }

  public static function reserveId($wish) {
    $wish = trim(strip_tags($wish));
    $name = preg_replace('|[^a-zA-Z0-9]|', '-', $wish);
    $c = 0;
    while (in_array($name.$c, self::$ids)) $c++;
    self::$ids[] = $name.$c;
    return $name.$c;
  }
}

class BootstrapRow {
  private $columns = array();
  private $classes = "";

  public function __construct($classes) {
    $this->classes = $classes;
  }

  public function col($content, $size, $classes = array()) {
    $this->columns[] = array($content, $size, $classes);
    return $this;
  }

  public function __toString() {
    $output = '<div class="row';
    if (!empty($this->classes)) $output .= ' '.$this->classes;
    $output .= '">';
    foreach ($this->columns as $col) {
      $col[2][] = 'col-md-'.$col[1];
      $output .= '<div class="'.implode(" ", $col[2]).'">';
      $output .= $col[0];
      $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
  }
}

class BootstrapNavTab {
  private $tabs = array();
  private $classes;

  public function __construct($classes = "") {
    $this->classes = $classes;
  }

  public function tab($headline, $content, $title = false) {
    $this->tabs[] = array($headline, $content, $title);
    return $this;
  }

  public function __toString() {
    $tabs = array();
    $panes = array();
    foreach ($this->tabs as $tab) {
      $title = ($tab[2])? ' title="'.$tab[2].'"' : '';
      $id = Bootstrap::reserveId($tab[0]);
      $active = (count($tabs) == 0)? ' class="active"' : '';
      $tabs[] = '<li'.$active.'><a'.$title.' href="#'.$id.'" data-toggle="tab">'.$tab[0].'</a></li>';
      $active = (count($panes) == 0)? ' active' : '';
      $panes[] = '<div class="tab-pane'.$active.'" id="'.$id.'">'.$tab[1].'</div>';
    }

    $output = '<ul class="nav nav-tabs">';
    $output .= implode($tabs);
    $output .= '</ul>';
    $output .= '<div class="tab-content '.$this->classes.'">';
    $output .= implode($panes);
    $output .= '</div>';
    return $output;
  }
}
