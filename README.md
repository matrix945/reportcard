# MoodleSchoolFilePlugin

# Dependence:
1)pdftk tools https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/


# TODO:
1) Remove Hard code file location (fdf templete file; generated reportCard locations  )
2) Remove Hard code student id and its email (56 , 2121373869@qq.com) 
3) Rename reportcard templete filelds name
4) Fix grid character size and font
5) Add more infornamtion to Moodle (Instructor name, course full name)
6) Separate file based on Moodle Role Based Control System
7) Fix shell commend for ubuntu
8) Enhance query Currently, using student email
9) Fix some UI ([[reportcard]][[nav_name]])
10) ??? action_page.php page need AJAX or find a way to pass all string to another php page

# Install
1) install pdftk first[install guide link] (https://linuxhint.com/install_pdftk_ubuntu/) [install issue link in case] (https://askubuntu.com/questions/1028522/how-can-i-install-pdftk-in-ubuntu-18-04-and-later)
2) Test sever `pdftk`
..* install from git -> `git clone`
..* Move the folder under /moodle/local
3) change all folder to www-data `sudo chown -R www-data:www-data /var/www/html/moodle/local/reportcard/`
4) Refresh moodle home page the installation will pop up
5) Create the pdf temeplete file *.fdf first in the target location `pdftk temeplete.pdf generate_fdf output data.fdf`

OR
3) Download the zip package from git
4) drop it to Plugin installer `/admin/tool/installaddon/index.php`

# how to use?
1) Login as admin
2) Site administration -> Plugins tab -> [[nav_name]]
3) Input a student email
4) select student term grades
5) click submit button
