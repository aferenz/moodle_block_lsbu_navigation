moodle-block_lsbu_navigation
=================================

London South Bank University (LSBU) require a customised 'navigation' block for their Moodle:

 - navigation links dependent on context (e.g. 'personal details' are displayed on the My Moodle page) pages (a.k.a. 'landing' pages).
 - hidden courses should be shown to students but are not accessible.
 - access to 'Site home', 'Site pages' and 'My profile' should be configurable.
 - Moodle courses should be grouped based on LSBU course type. These groups are, in turn, grouped by academic year.
 
This block requires the LSBU API (moodle-local_lsbu_api) for the bulk of its functionality, especially the 'My courses' navigation tree.
