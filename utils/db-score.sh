#!/bin/bash
./bin/console doctrine:mapping:convert --from-database --em=score --namespace='Entity\Score\' annotation ./src > /dev/null
for i in $(ls src/Entity/Score/); do sed -i  's/Entity\\Score/App\\Entity\\Score/g' src/Entity/Score/${i}; done > /dev/null
