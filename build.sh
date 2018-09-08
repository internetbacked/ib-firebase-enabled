#!/usr/bin/env bash

composer dump

rm -f dist/ib-firebase-enabled.zip
zip -r dist/ib-firebase-enabled.zip ./ -x "*.git*" -x  "*.idea*"