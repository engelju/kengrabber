/**
 * Copyright 2015 Simon Erhardt <me@rootlogin.ch>
 *
 * This file is part of kg.
 * kg is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * kg is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with kg.
 * If not, see http://www.gnu.org/licenses/.
 */
(function($,angular) {
    'use strict';

    var kg = angular.module('kengrabber',[
        'ngRoute',
        'ngAudio',
        'ui.bootstrap'
    ], ['$compileProvider',
        function($compileProvider){
            $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|itpc|feed):/);
        }
    ]);

    /** CONFIG **/

    kg.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                when('/', {
                    templateUrl: 'home.html',
                    controller: 'HomeCtrl',
                    resolve: {
                        channel: ['$q','Channel', function($q, Channel) {
                            var defObj = $q.defer();
                            Channel.get(function(channel) {
                                defObj.resolve(channel)
                            });
                            return defObj.promise;
                        }]
                    }
                }).
                when('/track/:trackId', {
                    templateUrl: 'track.html',
                    controller: 'TrackCtrl',
                    resolve: {
                        channel: ['$q','Channel', function($q, Channel) {
                            var defObj = $q.defer();
                            Channel.get(function(channel) {
                                defObj.resolve(channel)
                            });
                            return defObj.promise;
                        }]
                    }
                }).
                otherwise({
                    redirectTo: '/'
                });
        }
    ]);

    /** FACTORIES **/

    kg.factory('Channel', ['$http','$filter',
        function($http,$filter){
            return{
                get: function(callback){
                    $http.get('res/channel.json').success(function(data) {
                        $.each(data.tracks, function(index, track) {
                            // add some variables
                            track.index = index;
                            track.descriptionShort = splitString(track.description, 200) + "...";
                            track.published = new Date(track.published * 1000);
                        });

                        data.tracks = $filter('orderBy')(data.tracks,'-published');

                        callback(data);
                    });
                }
            };
        }
    ]);

    /** FILTER **/

    kg.filter('secondsToDateTime', function() {
        return function(seconds) {
            var d = new Date(0,0,0,0,0,0,0);
            d.setSeconds(seconds);
            return d;
        };
    });

    kg.filter('raw', ['$sce', function($sce){
        return function(val) {
            return $sce.trustAsHtml(val);
        };
    }]);

    /** CONTROLLER **/

    kg.controller('HomeCtrl', ['$scope','channel',
        function($scope,channel) {
            $scope.channel = channel;
            $scope.podcastUrl = window.location.host + window.location.pathname + "podcast.rss";

            // Pagination stuff
            $scope.filteredTracks = [];
            $scope.currentPage = 1;
            $scope.numPerPage = 9;
            $scope.maxSize = 5;

            $scope.$watch("currentPage + numPerPage", function() {
                var begin = (($scope.currentPage - 1) * $scope.numPerPage);
                var end = begin + $scope.numPerPage;

                $scope.filteredTracks = channel.tracks.slice(begin, end);
            });


            window.scope = $scope;
        }
    ]);

    kg.controller('TrackCtrl', ['$scope','$routeParams','channel','ngAudio',
        function($scope,$routeParams,channel,ngAudio) {
            var trackId = parseInt($routeParams.trackId);
            var track = channel.tracks[trackId];
            $scope.track = track;
            $scope.audio = ngAudio.load(track.url);
            console.debug($scope.audio);
        }
    ]);

    /** FUNCTIONS **/

    function splitString(string, splitStep) {
        var length = string.length,
            array = [],
            i = 0,
            j;

        while (i < length) {
            j = string.indexOf(" ", i + splitStep);
            if (j === -1) {
                j = length;
            }

            array.push(string.slice(i, j));
            i = j;
        }

        return array[0];
    }
})(jQuery,angular);