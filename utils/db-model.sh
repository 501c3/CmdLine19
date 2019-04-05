#!/bin/bash
./bin/console doctrine:mapping:convert --from-database --em=model  --namespace='Entity\Model\' --force --no-debug annotation ./src
for i in $(ls src/Entity/Model/); do sed -i  's/Entity\\Model/App\\Entity\\Model/g' src/Entity/Model/${i}; done > /dev/null
