$HeadURL$
$Id$

		Welcome to XTemplate: a templating engine for PHP.
				http://www.phpxtemplate.org/

Purpose:

	- To enable the abstraction of PHP code logic from the display layer (HTML
	  etc).
	Or:
	- To enable coders to do what they do best, code and designers to
	  concentrate on their forte, presentation.

Features:

	- Simple variable interpolation ( {STUFF} )

	- Array variable interpolation
	 	e.g. {ROW.ID} {ROW.NAME} {ROW.WHATEVER}, you assign an array to the
	 	variable ROW once

	- Global PHP variables available without making assigns
	 	e.g. {PHP._SERVER.HTTP_HOST} {PHP._SERVER.PHP_SELF} or any variable:
	 	{PHP._COOKIE.PHPSESSID}

	- Assign using arrays
		e.g. $xtpl->assign(array('STUFF' => 'kewlstuff', 'ROW' => $row));

	- Default nullstring for unassigned variables and unparsed blocks,
	 	custom nullstring for every variable or block

	- Dynamic blocks:
		- Multiple level dynamic blocks (nested blocks)
		- Autoreset feature (do not reset subblocks when reparsing parent
		  block)
		- Recursive parse to parse all subblocks of a block

	- File (template) include: {FILE "another.template.xtpl"}, the included
		- templates can also contain includes recursively..
		- uses fopen so you can include http:// and ftp:// files too

	- {FILE {VAR}} - you can assign filenames on the fly for {VAR}

	- On big projects you have the ability to substitute files with some other
	  templates

	- Very simple and powerful syntax, you'll love it :)

Documentation:

	For first time documentation, see the included example files (ex1.php -
	ex9.php) and their associated templates (*.xtpl)
		(cranx tried to write them so even newbies should understand easily)

	You should also find a docs folder with this distribution containing
	phpDocumentor (http://www.phpdoc.org) output.

	Other documenation can be found at http://www.phpxtemplate.org/

Installation:

	Nice and straight forward, just have upload it and include it - that's it!

Upgrade:

	(stating the obvious - but some people seem to forget!)

	As with any software evaluate the upgraded version on some test code before
	deployment, then backup the previous version - in case there's any
	problems, then copy the new version over the old. Test, rollback to your
	backup (you did make a backup didn't you?!?) version if there's an issue,
	check the bug list and report if it's a new bug you've discovered.

History:

The code is very short and very optimized, according to my speed tests it's the
fastest template engine around which can do nested blocks and variables..

The basic syntax is from FastTemplate and QuickTemplate
(http://phpclasses.upperdesign.com/browse.html/package/49), but the entire
class was written by cranx from scratch in one day, without a line from other
template engines. THIS IS NOT A REWRITE OF OTHER ENGINES!

The algorithm used makes this code amazing fast, you can do a bunch of
nested dynamic blocks and everything you want, because it doesn't use recursive
calls to build up the block tree.

Docs and some functions we didn't need (clearing variables, etc) are still
missing, but they'll come if there's demand.

Sometime around 2002, cranx was too busy to continue with the project, so
cocomp took over (kind of!), cocomp was then too busy for about, erm, 3 years!
Anyway, cocomp has found a new lease of life for the project and rolled up the
last few years improvements and released them on Sourceforge. Enjoy. If you
want to get involved in XTemplate, see http://www.phpxtemplate.org/

Latest stable & Subversion versions always available @
http://sourceforge.net/projects/xtpl/

Copyright (c) 2000-2001 Barnabas Debreceni [cranx@users.sourceforge.net]
Copyright (c) 2002-2007 Jeremy Coates [cocomp@users.sourceforge.net]

Licensed BSD / LGPL - see license.txt

--
END