# Lp2Lrs Plugin

[TOC]

## About

In order to generate a sufficient amount of data in a Learning Record Store (LRS) for Learning Analytics with little implementation effort on the
ILIAS side, we propose to transfer every object-related learning progress change in ILIAS to a Learning Locker LRS.  

## Features

Each learning progress change in ILIAS is sent to a predefined learning locker client (LRS). It includes
- information about the object itself (ID, type, title, time, if possible score), 
- keywords (LOM Metadata) and 
- information about the parent object (ID, type, title - if it's a course, group or category).
The learning progress changes are stored in a separate table and transmitted via a cron job to the LRS as an xAPI statement  



# Prerequisites

- ILIAS 7.x, 6.x
- PHP 7.3
- MySQL 5.7



# Installation

The plugin must be integrated into the following subdirectory of the ILIAS document root using command line and git:

```
mkdir -p /[ILIAS_DOC_ROOT]/Customizing/global/plugins/Services/Cron/CronHook
cd /[ILIAS_DOC_ROOT]/Customizing/global/plugins/Services/Cron/CronHook
git clone https://github.com/internetlehrer/Lp2Lrs
```

With your web browser open ILIAS and login as system administrator. 
Navigate to `Administration > Extend ILIAS > Plugins` to install and activate the plugin.



# Configuration

In an additional browser tab open ILIAS and navigate to `Administration > Extend ILIAS > xAPI/cmi5` and add a new LRS-Type.

Switch to the browser tab to administrate your plugin and call the configure action. Select the just added LRS-Type and save your settings.



# Cronjob

Every change of the ILIAS learning progress (also courses) to 'in progress', 'completed', 'failed' is first
logged in the table 'lp2lrs_queue_lpchanged'. At the times defined in the CronJob, the entries are enriched and sent as xAPI statements to the Learning Record Store. 
Therefore it is recommended to set the Schedule to 1 minute in the administration of CronJobs.


# Usage

Activate the learning progress options in administration and objects. That's it - the Lp2Lrs Plugin is ready to generate and send the statements to your learning record storage.

## Keywords

Learning Object Metadata (LOM) are supported by many ILIAS objects. Thus, a content assignment
for learning analytics is possible for the objects in which keywords were entered!
And special for LTI Objects: Keywords are set by ILIAS LTI Provider settings and are taken over
automatically to the ILIAS LTI Consumer Settings. Users could forget to set keywords.

We recommend keywords related to
- content assignment (topics)
- learning requirements
- level of difficulty.
Furthermore, we recommend the use of decimal classifications. An example of a widely used decimal classification is the Dewey Decimal Classification (DDC) (see https://www.oclc.org, https://www.dnb.de/DE/Professionell/DDC-Deutsch/ddc-deutsch_node.html).


## Context

In addition to considering the keywords at the granular level, we also recommend considering
the organisational and methodological context in which the resources are integrated. 
This applies primarily to the ILIAS course and the ILIAS group. Both have user administration.

In addition, the use of resources seems interesting in comparison and context to other
resources. For example, existing prior knowledge and/or digital literacy can be decisive for learning
success and the effective use of resources.


# Privacy

To restrict the transfer of data to courses and objects within them, please use the plugin 'Lp2LrsPrivacy'.
