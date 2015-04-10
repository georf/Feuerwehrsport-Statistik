<?php

Check2::page()->isSubAdmin();

Cache::clean();


new ChartLoader();
$myCache = new pCache();
$myCache->removeOlderThan(1);

echo Title::h1("Gelöscht");
echo Link::admin_page_a("overview", "Überblick");