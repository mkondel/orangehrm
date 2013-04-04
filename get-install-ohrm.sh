git clone git@github.com:mkondel/orangehrm.git
cd orangehrm
git flow-setup
git pull origin feature/weekly_report
git branch --set-upstream feature/weekly_report origin/feature/weekly_report
git checkout feature/weekly_report
chmod -R a+w lib/confs lib/logs symfony/config symfony/apps/orangehrm/config symfony/cache symfony/log
