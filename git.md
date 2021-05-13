# Organisation des branches

## v2-albertville

Branche de production en ligne sur `https://tra-inscriptions.com`

## hotfix-albertville

Branche qui clone  __v2-albertville__  pour des corrections de bugs.

Elle doit être mergée dans les branches  __v2-albertville__  et  __travail-albertville__ puis supprimée.

## travail-albertville

Branche qui clone  __v2-albertville__  pour des développements en cours.

Elle doit être mergée dans  __release-albertville__  et mise en ligne sur `https://test.dafap.fr` afin de tester et debugger avant mise en production.

## release-alvertville

Branche qui clone  __travail-albertville__  pour des corrections de bugs.

Elle doit être mergée dans les 3 autres branches puis supprimée.