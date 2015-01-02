PHP Comment Reporter
Introduction:
While working as a Team Lead for Barnes and Nobel, code reviews were consuming lot of my time and I looked for some tool
which can save my time, not getting what I needed, I created this simple but useful tool and decided to share it with you.

Tools is developed in PHP with purpose to check and report uncommented lines of code in files.Such type of review is needed 
specially when you are working on complex projects.

Objective is to report complex projects and reduce code review time. 
The tool is capable to traverse complex directories structure, identify PHP source code and generate report. 

Why a new tool:
There were comment reporting tools available, but they simply state the percentage of commented and uncommented lines. 
As a developer and code reviewer, I found it useless and write my own code. This tool will parse each line of code and check for comments. 
It will finally generate a report, marking each uncomment line of code with complete file path. It raise red flag for files which are 
crossing threshold value. This tool will eliminate blank lines and comments while calculating percentage for accuracy purpose

Ease of use:
The tool is one file of php code , that can be easily integrated your application.Simply provide name of file or folder in $scaning_file_name variable 
and you will get a complete report.

Limitation/ToDO:
In single PHP statement, which is wrapped in multiline having double quote, this tool will treat each wrapped line, a new line. For example this mySql:
$sql = "UPDATE `key_values` SET
                `Value_Content` = '" . $this->db->escape($revisionValues['value']) . "',
                `Comments` = '" . $this->db->escape($revisionValues['comment']) . "',
                `Is_Active` = '" . $this->db->escape($revisionValues['actstate']) . "',
                `Is_Modified`='1'    WHERE `Value_ID` = '$valueID'";
