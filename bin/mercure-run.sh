#!/bin/bash

# save the current dir of the user to navigate back to it once the script is done
LAST_USER_DIR=$(pwd)

# navigate into the directory this script is located in
SCRIPT_PATH="$(realpath "${BASH_SOURCE[0]}")"
SCRIPT_DIR="$(dirname "$SCRIPT_PATH")"
cd $SCRIPT_DIR

if [ "$(uname -s)" == "Darwin" ]; then
    JWT_KEY='5bf5d5f2dfae61c3ae78e323315c470597d937b0f33d456a5c77c65a2b2b551fdc1223c361a781c8037934a089b9f6df39450cbbd0fa351eecf1321589f2c4b20b27734af2c32797a812665c2ae563290b39c3bd350a92110993d48f784557242f89adcaac5d9e7b79ddf582ae65081a2de6c522d961209393536aad3e08106bab2285e60bdb28d8c65216a204daef87c1434b338236b4cc1137edd2e9a8c945c1c2041ab162f9deab452017dcd1a8bf0c26626b1ec24af1287eb61a073b350bb23831e8df6fd337233938cdcaaae79c7a2f5162e59b665259acbb941b7be1cf7167609436e7c3c93dac97ff803e4d63b7846059a26d460b7cadae4ff077185bf7c95d7b014b2364fd51171225052958e21c165e4afb66f4321470c2f46f5461' \
    ADDR=':3001' \
    CORS_ALLOWED_ORIGINS='http://127.0.0.1:8000 http://127.0.0.1:8001' \
    PUBLISH_ALLOWED_ORIGINS='http://localhost:3001' \
    USE_FORWARDED_HEADERS=1 \
    ./mercure-executables/mercure-mac
fi

if [ "$(uname -s)" == "Linux" ]; then
    JWT_KEY='5bf5d5f2dfae61c3ae78e323315c470597d937b0f33d456a5c77c65a2b2b551fdc1223c361a781c8037934a089b9f6df39450cbbd0fa351eecf1321589f2c4b20b27734af2c32797a812665c2ae563290b39c3bd350a92110993d48f784557242f89adcaac5d9e7b79ddf582ae65081a2de6c522d961209393536aad3e08106bab2285e60bdb28d8c65216a204daef87c1434b338236b4cc1137edd2e9a8c945c1c2041ab162f9deab452017dcd1a8bf0c26626b1ec24af1287eb61a073b350bb23831e8df6fd337233938cdcaaae79c7a2f5162e59b665259acbb941b7be1cf7167609436e7c3c93dac97ff803e4d63b7846059a26d460b7cadae4ff077185bf7c95d7b014b2364fd51171225052958e21c165e4afb66f4321470c2f46f5461' \
    ADDR=':3001' \
    CORS_ALLOWED_ORIGINS='https://know-your-project.philippmartini.de:443' \
    PUBLISH_ALLOWED_ORIGINS='https://know-your-project.philippmartini.de:3001' \
    USE_FORWARDED_HEADERS=1 \
    ./mercure-executables/mercure-linux > /dev/null 2>&1
fi

cd $LAST_USER_DIR