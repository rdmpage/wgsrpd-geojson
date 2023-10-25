# World Geographical Scheme for Recording Plant Distributions in GeoJSON

This repository contains [GeoJSON](https://en.wikipedia.org/wiki/GeoJSON) files for the four different levels of the [World Geographical Scheme for Recording Plant Distribution](https://en.wikipedia.org/wiki/World_Geographical_Scheme_for_Recording_Plant_Distributions). This is a TDWG standard, see [World Geographical Scheme for Recording Plant Distributions (WGSRPD)](https://github.com/tdwg/wgsrpd). The repository for the standard details the history of this recording scheme, and GeoJSON files for each of the four geographic levels. I originally made those GeoJSON from GIS files retrieved from a Kew website that is now defunct (see [rdmpage/prior-standards](https://github.com/rdmpage/prior-standards/tree/master/world-geographical-scheme-for-recording-plant-distributions) for details). The licensing of those files is unclear, which may limit their utility. Progress on the TDWG standard seems uncertain, so I’ve created this repository so that anyone wanting to use these regions with an open license can do so.

## Data source

The [RBGKew/powop](https://github.com/RBGKew/powop) repository for [Plants of the World Online](https://powo.science.kew.org) has a AGPL-3.0 license. In this repository there is a [MySQL dump](https://github.com/RBGKew/powop/blob/production/powo-geodb/data/data.sql) of the World Geographical Scheme for Recording Plant Distributions. I have downloaded that file, created a MySQL database, extracted the plant regions in [Well-known text](https://en.wikipedia.org/wiki/Well-known_text_representation_of_geometry) (WKT) and used [ogr2ogr](https://gdal.org/programs/ogr2ogr.html) to generate GeoJSON for each region. These files (and any code I have authored in this repository) is also available under the terms of the AGPL-3.0 license.

## GeoJSON conversion

The key steps for converting WKT to GeoJSON are described in [this answer](https://gis.stackexchange.com/a/441877) to a question on [GIS StackExchange](https://gis.stackexchange.com/questions/441875/convert-a-wkt-string-within-a-text-file-to-geojson-using-ogr2ogr-or-gdal-command).

To generate GeoJSON files to display on the web we need to convert between the [Spatial reference system](https://en.wikipedia.org/wiki/Spatial_reference_system) used in the MySQL database and that used by web viewers. The MySQL data uses  [EPSG:3857](https://epsg.io/3857), the GeoJSON files use [EPSG:4326](https://epsg.io/4326). Hence when converting WKT to GeoJSON using `ogr2ogr` we need the options `-s_srs EPSG:3857 -t_srs EPSG:4326`.

We also need the flag `-lco RFC7946=YES` to ensure they follow the [“right-had rule”](https://gis.stackexchange.com/questions/259944/polygons-and-multipolygons-should-follow-the-right-hand-rule) in [The GeoJSON Format RFC7946](http://doi.org/10.17487/RFC7946). This avoids errors when using tools such as [GeoJSONLint](https://geojsonlint.com).

The GeoJSON files have been generated with one file per region, and files for each level have been stored in the corresponding folder (`level1`, `level2`, etc.). The GeoJSON output from `ogr2ogr` as been formatted using PHP `json_encode` and has the code and name for the corresponding regions included as feature `properties`.

The file `paths.json` has an associative array listing each the files in each level by their code, making it easier to find a file from its level-specific code.

## Viewing GeoJSON

The websites [geojson.io](http://geojson.io/) and [GeoJSONLint](https://geojsonlint.com) are good places to view GeoJSON files.  GitHub also has built-in GeoJSON viewing for smaller files, which you can see [here](level1/2-AFRICA.json), for example.


