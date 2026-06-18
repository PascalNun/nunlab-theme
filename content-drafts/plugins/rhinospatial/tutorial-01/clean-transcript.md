# RhinoSpatial Workflow Tutorial

Hi, this is a small workflow tutorial for RhinoSpatial.

RhinoSpatial is a Grasshopper plugin for working with site context directly in Rhino. We're going to start with the installation.

## Installation

The easiest way to install RhinoSpatial is via the Package Manager. Make sure to include pre-releases when searching, because right now it is in alpha. Search for RhinoSpatial, and there it should be. Just click Install.

You can also install it via the Components folder.

I preloaded some datasets in this definition, but everything else I'm going to do from scratch.

## Spatial Context

The plugin is centered around the Spatial Context component. The basic idea behind it is that we have a lot of datasets we want to align to the same spatial coordinates.

The Spatial Context component has a few inputs. Only one input is needed, and that is Open Map. We open it via a button. Other inputs, like Use Absolute Coordinates or Reference URL, are not needed.

By default, the Spatial Context component loads everything near Rhino's 0,0 point. Rhino works best around the 0,0 point, but you might want to use the absolute coordinates. For that, we have the Boolean toggle here.

Another helpful input is the Reference URL. For example, I can choose this WFS layer as a Reference URL, and then the map is preselected around the same area as this dataset.

To select our area, we're just going to click the button. A browser window opens, and here you can see the preselected area. Then we can select the area. I'm going to select this area around the river. You can resize afterwards. Once you've selected the area, you can just close the window.

Now we have loaded the Spatial Context.

## Load WFS

If we now want to load the vector data here, we're going to need the Load WFS component. It can load a few different file types: some local, some URL-based.

As I said, you don't really need the Reference URL, so you can disconnect it right now.

Usually vector datasets have a few layers, and for that we have the List Layers component. In this case, we want to list the vector layers, which is the List WFS Layers component. I know that there is just one layer in there, which we can see if we add a panel. You only see the one layer.

So we can just connect the layer and the WFS URL. The last thing we need is the Spatial Context component. Now it loaded all the vector data around the area we selected.

## Load WMS

Next I want to load in some WMS data. In our case, it's an aerial image.

For that, we need the Load WMS component and a List WMS Layers component. I know for a fact that this link has a few layers in there, so we can look at them via the panel.

We want to select layer five, or rather layer six here. We're going to do this via the List Item component and a Number Slider. We can now connect the layer.

You can only ever load one WMS layer. For vector layers, you can load as many as you want via just one component.

Then we need to connect the URL and, of course, most importantly, the Spatial Context component. There we have an aerial image aligned to the vector data.

## Load GeoTIFF

Next I want to load some GeoTIFF files. For that, I am going to disable the aerial image here.

The Load GeoTIFF component expects a local GeoTIFF file. It's pretty easy: just connect the local file via a File Path component. And, of course, we need our Spatial Context again. There we have it loaded in.

One thing, of course, is that it only loads georeferenced GeoTIFF files. If it can't read the EPSG data, we can't really put it in the right Spatial Context.

## Load Terrain

Next I want to load some terrain data. For that, we need the Load Terrain component. Load Terrain, of course, needs the Spatial Context.

The Load Terrain component has some fallback data. As you can see, it's not that fine, but it also accepts a few data sources: for example WCS URLs, local DEM files, and a few more basic terrain grids. You can always read up on GitHub what it can load.

I prepared this URL here, which should be a bit better data. There we go.

## Load LoD2 Buildings

Next I want to load in some LoD2 data. There we have a few options again. We have WFS or LoD2 links. We can load in some CityGML or CityJSON data. In general, most LoD2 data in a ZIP archive or folder can be loaded in.

For loading in the LoD2 data, we need the Load LoD2 Buildings component. We need the source data. We can select a layer here, but we don't need to. And, of course, we need the Spatial Context.

This might load a bit. And there we have it. The LoD2 data is loaded in. It took a fairly long time to load in. Now we can work with it, of course. For example, we can bake it.

## Load OSM

Next is something a bit different. We're going to load in some OpenStreetMap data via the Load OSM component.

The OSM component loads OpenStreetMap data via the Overpass API, so the only thing you really need is the Spatial Context component. Also, you need to select what things you want to load in via a Boolean toggle.

I want to load in all the data, so I'm going to select True and just select all of the things. Now we just need to connect the Spatial Context component. Here again, this might load for a fairly long time.

Okay, that took a long time, but now it's loaded in. I'm going to hide the LoD2 data for a second here. By default, buildings are always loaded in. You just need to select the other ones.

I'm going to display it a bit better. And there we have it displayed a bit better.

One thing you see here is that the Spatial Context component aligns everything, or rather every 3D element, in 3D space. So the LoD2 data usually sits at the same height as the OSM data. But all the 2D data, which includes, in this case, the greenery or water, is displayed in one plane.

If we load in the terrain data, we should see that the OSM buildings are aligned to the terrain fairly well. And yeah, we can see it here.

The buildings not looking right here are just mesh rendering artifacts. If you bake the buildings, they should be displayed correctly, because the data is right. It's just a display issue here. I think the mesh count is too low.

## 3D Tiles Viewer (Google)

As a bit of a bonus, here is the last component. It's the 3D Tiles Viewer (Google) component. It's under a new tab because it's under the View tab.

It doesn't allow you to actually download the data. You can only display the data, and that's what we are doing here. We are basically aligning a Google Maps, or rather Google Earth, preview to our Spatial Context.

What we need for that is a Google API key, or rather a Map Tiles API key. As I said, you can only use the data for display purposes. Google doesn't allow you to save it. I've loaded my API key in here.

We of course want it in the same Spatial Context, so we are going to load the Spatial Context. By default, it is disabled because it takes a fairly long time to load. To enable it, you just need a Boolean toggle again. There it is loaded in.

As you can see, it is a bit buggy. Sometimes you need to reselect the Spatial Context because right now it only loads half. I clearly need to work a bit more on it.

Let me turn off the preview here. You can see it's not a lot selected here. You can try to rerun it, or usually it helps to reselect the area.

By the way, baking doesn't really work. You can click on it, but it doesn't bake anything, because, as I've mentioned, you are not really allowed to save the data. You can only display it and look at it in the context in this case.

You might want to see if something you have modeled is aligned to it, or rather what it would look like behind the things you have modeled.

And that's basically all the functionality.
