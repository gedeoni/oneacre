/* eslint-disable */
process.env.NODE_ENV = '"production"';
var express = require("express");
var app = express();
var http = require("http").Server(app);
var path = require("path");
const history = require('connect-history-api-fallback')
app.use(history({
    // verbose: true
}));
var port = process.env.PORT || 3000;

// handle fallback for HTML5 history API
//app.use(history())
//Serve static pure assets
app.use("/", express.static(__dirname + "/dist"));


//app.use(history("index.html", { root: root }))
//app.use(history("index.html", { root: root }));
module.exports = http.listen(port, function (err) {
    if (err) {
        console.log(err);
        return;
    } else {
        console.log("Gedeon server running");
        console.log("listening at " + port);
    }
});