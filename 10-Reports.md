# Reports <a id="reports"></a>

## Quickstart <a id="reports-quickstart">

To create your first report, click `New Report` in the `Reports` tab. You have to fill out a form to define the properties of the report you want. Some of the fields will only show up after you have chosen the according report type.

* *Name*: A unique name for your report. Use `test-report` for the quickstart
* *Timeframe*: Choose from the predefined timeframes or create a new one in tab `Time Frames`. Use `Current Day` for the quickstart
* *Template*: You can define templates to customize your reports. Leave empty for the quickstart
* *Report*: Specify which type of report you want to create (`Host SLA` for the quickstart):
  * *System*: A simple test report which gives details about the PHP installation
  * *Host SLA*: SLA calculation for your host objects
  * *Service SLA*: Like Host SLA just for Service objects
* *Filter*: Filter for objects to list and use for calculation. You can use Icinga Web 2 style filtering. Leave empty for the quickstart
* *Breakdown*: Break down report in other timeframes. Leave empty for the quickstart
* *Threshold*: Percentage of uptime your SLA guarantees

After filling out the form click `Create Report` and you'll end up in the list of created reports. Click your report `test-report` in the list to view it. You can use the links on top to `Modify`, `Schedule`, `Download` or `Send` it.
