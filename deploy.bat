@echo off
echo Degisiklikler Git'e ekleniyor...
git add .

echo.
echo Commit mesajini girin:
set /p commit_message=

echo.
echo Degisiklikler commit ediliyor...
git commit -m "%commit_message%"

echo.
echo Heroku'ya push'laniyor...
git push heroku main

echo.
echo Deploy islemi tamamlandi!
pause 