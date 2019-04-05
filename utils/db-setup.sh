#!/bin/bash
./bin/console doctrine:mapping:convert --from-database --em=setup  --namespace='Entity\Setup\' --force --no-debug annotation ./src
#for i in $(ls src/Entity/Setup/); do sed -i  's/Entity\\Setup/App\\Entity\\Setup/g' src/Entity/Setup/${i}; done > /dev/null
