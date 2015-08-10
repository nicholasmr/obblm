# Introduction #

This page describes how DoubleSkulls manages the divergence between the OBBLM code and the customisations for the SLOBB and ECBBL leagues.

Many implementations of OBBLM will have customisation to the core code that may not be appropriate to integrate into the core - even as a module. Having every league's custom features as modules may explode the number of modules and make for an unsupportable code base.

# Details #

## Multitenant or not ##
DoubleSkulls's two leagues do not actually have any real overlap. Both are tabletop leagues, one in London and the other in Sydney. The only commonality is DoubleSkulls himself! So without any shared user base having single tenanted installations seems a better as it provides a better basis for configuring the leagues to meet their own requirements.

Also the data migration from the old sites would have been much more complicated with the multi-tenanted solution as he'd have to have solved the lack of unique identifiers between the leagues (so both have teams with an ID of 1 for instance).

## Subversion and Branching ##

### Structure ###
DoubleSkulls code is part of the main SVN repository see http://obblm.googlecode.com/svn/branches/DoubleSkulls/
```
-branches
--DoubleSkulls
---trunk - contains the customisations for the sites
---tags
---branches
----ecbbl - contains configuration for ECBBL 
----slobb - contains configuration for SLOBB
```

To start off with DoubleSkulls created a branch from the main trunk at the then current head. This [branch](http://obblm.googlecode.com/svn/branches/DoubleSkulls/trunk) contains all the customisations to the code for his sites. i.e. all functional changes are contained in this site.

Then the two site sub-branches are created from the [branch trunk](http://obblm.googlecode.com/svn/branches/DoubleSkulls/trunk) and edited to provide the appropriate configuration specific to them (e.g. league logos).

### Merging ###
Using the TortoiseSVN client it is a relatively simple matter to incorporate changes "downstream" i.e. that is from the trunk to the DS branch and from the DS branch into the sites. The standard "merge a range of revisions" can be used to get change from upstream versions into downstream. Since subversion is relatively clever it means that only the lines of code that are changed are propagated down. So for example once the database connection properties are set and committed within a downstream branch they will not be modified or overwritten, unless those lines have been modified in an upstream file. So you db password only gets overwritten if it gets changed upstream.

### Database Changes ###
Currently still reliant on the database upgrade functions within modules.