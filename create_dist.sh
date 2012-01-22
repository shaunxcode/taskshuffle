rm -rf ./dist
mkdir ./dist
cp -r ./bin ./dist/bin
cp ./index.js ./dist/bin
cp -r ./lib ./dist/lib
cp ./package.json ./dist/package.json
cp -r ./public ./dist/public
cp README ./dist/README