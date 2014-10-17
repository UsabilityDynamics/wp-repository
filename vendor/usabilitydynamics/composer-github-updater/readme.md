### Usage
```bash
curl http://repository.usabilitydynamics.com/repository/updater/?github_access_token=HASH&organizations=ORGANIZATIONS_VIA_COMMA
```

After triggering an update, you should be able to see all the tagged versions of a composer library:
```bash
composer show usabilitydynamics/wp-veneer
```

You should see the following:
![wp-veneer-example](http://content.screencast.com/users/TwinCitiesTech.com/folders/Jing/media/cf9af477-5007-444a-b3db-ed4b8bec897c/00000073.png)


### Summary
Synchronizes GitHub repository with local static files so that we can easily
create composer repositories from GitHub repos. Extracts the composer.json file
from each repo and then formats it in the format required by composer repositories.

Use the following path to init the script:
repository.usabilitydynamics.com/repository/updater/

Use the following path to init the script without using any caching:
```sh
curl http://repository.usabilitydynamics.com/repository/updater/?nocache
```

