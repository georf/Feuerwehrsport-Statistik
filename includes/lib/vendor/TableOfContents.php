<?php

class TableOfContents {
  public static function get() {
    return new TableOfContents();
  }

  private $items = array();

  public function link($anchor, $name, $title = false) {
    $this->items[] = array($anchor, $name, $title);
    return $this;
  }

  public function sub($toc) {
    $this->items[] = $toc;
    return $this;
  }

  public function __toString() {
    $output = '<div class="navbar-default toc">';
    $output .= '<h4>Inhaltsverzeichnis</h4>';
    $output .= '<ol>';
    foreach ($this->items as $item) {
      if ($item instanceof TableOfContents) {
        $output .= $item;
      } else {
        $output .= '<li><a href="#'.$item[0].'"';
        if ($item[2]) $output .= ' title="'.$item[2].'"';
        $output .= '>'.$item[1].'</a></li>';
      }
    }
    $output .= '</ol>';
    $output .= '</div>';
    return $output;
  }
}