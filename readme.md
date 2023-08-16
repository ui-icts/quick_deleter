Quickly delete and/or restore projects in batches.

-Delete and restore in the same submit.

-Projects deleted via Quick Deleter aren't actually deleted, but merely flagged for deletion 30 days later.

-All project deletes/restores will be logged at the project and system level individually.

Browse projects with 2 built in reports

1. My Projects
2. All Projects

-Or view a custom report by entering a list of pids
e.g. 2056,4365,364,14,8764

-Or paste in a json export of projects from the Admin Dashboard external module e.g.

{"header":["PID","Project Title","Status","Record Count","Research Purpose","PI Last Name","PI First Name","PI Email","IRB Number","Creation Time","Last Logged Event","Days Since Last Event"],"footer":null,"body":[["39","test4","Development","1","Basic or Bench Research, Clinical research study or trial, Translational Research 1, Translational Research 2","","","","","2023-04-04 20:11:27","2023-04-13 20:00:46","105"],["20","test3","Production","0","Basic or Bench Research, Epidemiology","","","","","2022-08-26 18:39:32","2022-08-26 18:50:29","335"],["19","test2","Analysis/Cleanup","0","Translational Research 1","","","","","2022-08-26 18:37:09","2023-02-08 18:19:59","169"],["18","test","Development","0","Basic or Bench Research","test","test","","","2022-08-26 18:36:56","2023-07-11 10:57:49","16"]]}

The json must be in this format. Quick Deleter will parse out the project ids.

-Questions, comments, bug reports: https://github.com/ui-icts/quick_deleter
