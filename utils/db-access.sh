#!/bin/bash
./bin/console doctrine:mapping:convert --from-database --em=access --filter=Person --namespace='Entity\Access\' annotation ./src > /dev/null
#for i in $(ls src/Entity/Access/); do sed -i  's/Entity\\Access/App\\Entity\\Access/g' src/Entity/Access/${i}; done > /dev/null