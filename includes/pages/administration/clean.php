<?php

Cache::clean();


new ChartLoader();
$myCache = new pCache();
$myCache->removeOlderThan(1);
