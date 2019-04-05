#!/bin/bash
./bin/console doctrine:mapping:convert --from-database  --em=sales --namespace='Entity\Sales\' annotation ./src > /dev/null
for i in $(ls src/Entity/Sales/); do sed -i  's/Entity\\Sales/App\\Entity\\Sales/g' src/Entity/Sales/${i}; done > /dev/null
