#!/bin/bash
./bin/console doctrine:mapping:convert --from-database  --em=competition --namespace='Entity\Competition\' annotation ./src > /dev/null
for i in $(ls src/Entity/Competition/); do sed -i  's/Entity\\Competition/App\\Entity\\Competition/g' src/Entity/Competition/${i}; done > /dev/null
