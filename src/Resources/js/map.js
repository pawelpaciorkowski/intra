'use strict'

const map = {
    mymap: null,
    markers: null,
    timer: null,
    collectionPoints: null,
    alabIcon: L.icon({
        iconUrl: '/img/marker.png',
        shadowUrl: '/img/marker-shadow.png',
        iconSize: [24, 40],
        shadowSize: [30, 18],
        iconAnchor: [12, 40],
        shadowAnchor: [0, 20],
        popupAnchor: [0, -40]
    }),

    init: function (cp, token) {
        const self = this;
        self.collectionPoints = self.addFlatStringToCollectionPoints(cp);

        $('.search input').focus();
        $('.search form').on('submit', function () {
            clearTimeout(self.searchTimer);
            self.search()
            return false;
        })

        this.mymap = L.map('map-cp');
        this.resetMap();

        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=' + token, {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            // id: 'mapbox/light-v10',
            id: 'mapbox/streets-v12',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: token,
        }).addTo(this.mymap);

        this.markers = L.markerClusterGroup({
            showCoverageOnHover: false,
        });

        for (let i = 0; i < self.collectionPoints.length; i++) {
            this.markers.addLayer(self.getMarker(self.collectionPoints[i]));
        }

        this.mymap.addLayer(this.markers);

        $('.page-header--map input[name=query]').on('keyup', function (e) {
            if (e.which === 27) {
                $(this).val('');
            }

            if (e.which === 13) {
                return;
            }

            clearTimeout(self.searchTimer);
            self.searchTimer = setTimeout(function () {
                self.search();
            }, 400)

        })

        $('.page-header--map input[name=query]').parents('form').on('submit', function () {
            return false;
        })
    },

    resetMap: function () {
        this.mymap.setView([51.9194, 19.1451], 7);
    },

    addFlatStringToCollectionPoints: function (cp) {
        for (let i = 0; i < cp.length; i++) {
            cp[i]['query'] = this.propertiesToArray(cp[i]).join().toLowerCase();
        }

        return cp;
    },

    getMarker: function (collectionPoint) {
        const marker = L.marker([collectionPoint.latitude, collectionPoint.longitude], {icon: this.alabIcon});
        marker.bindPopup(this.generatePopupContent(collectionPoint));

        marker.on('click', function (e) {
            e.target.openPopup();
        });
        marker.openPopup();

        return marker;
    },

    search: function () {
        const input = $('.page-header--map input[name=query]');
        const query = input.val().toLowerCase().split(' ');
        const markers = [];

        input.removeClass('input-error');
        this.markers.clearLayers();

        for (let i = 0; i < this.collectionPoints.length; i++) {
            let found = true;
            const self = this;
            query.forEach(function (word) {
                if (self.collectionPoints[i].query.search(word) === -1) {
                    found = false;
                }
            });

            if (found) {
                const marker = this.getMarker(this.collectionPoints[i]);
                this.markers.addLayer(marker);
                markers.push(marker);
            }
        }

        if (markers.length) {
            const group = new L.featureGroup(markers);
            this.mymap.fitBounds(group.getBounds());
        } else {
            input.addClass('input-error');
            this.resetMap();
        }

        if (markers.length === 1) {
            markers[0].openPopup();
        }
    },

    generatePopupContent: function (cp) {
        let content = '';

        if (cp.collectionPointPartnerLogo) {
            // content += '<img src="http://skarbiec.vm:8080/asset/collection-point-partner/'+cp.collectionPointPartnerLogo+'">';
            content += '<img src="https://skarbiec.alablaboratoria.pl/asset/collection-point-partner/' + cp.collectionPointPartnerLogo + '">';
        }

        content += '<strong>' + cp.name + '</strong><br>' +
            cp.street + '<br>' +
            cp.postalCode + ' ' + cp.city + '<br>';

        if (cp.phones) {
            content += 'tel.:';

            var first = true;
            for (const i in cp.phones) {
                if (!first) {
                    content += ', ';
                }
                content += '<a href="callto:' + cp.phones[i] + '" title="Zadzwoń na ten numer" data-toggle="tooltip">' + cp.phones[i] + '</a>';
                first = false;
            }

            content += '<br>';
        }

        if (cp.email) {
            content += 'e-mail: <a href="mailto:' + cp.email + '" title="Napisz email na ten adres" data-toggle="tooltip">' + cp.email + '</a><br>';
        }

        content += '<br>';

        const features = [];
        if (cp.isChildren) features.push(cp.isChildren);
        if (cp.isDermatofit) features.push(cp.isDermatofit);
        if (cp.isSwab) features.push(cp.isSwab);
        if (cp.isGynecology) features.push(cp.isGynecology);
        if (features.length) {
            content += '<strong>Cechy:</strong> ' + features.join(', ') + '<br>';
        }

        if (cp.marcel) {
            content += '<strong>Symbol:</strong> ' + cp.marcel + '<br>';
        }

        if (cp.mpk) {
            content += '<strong>MPK:</strong> ' + cp.mpk + '<br>';
        }

        if (cp.laboratory) {
            content += '<strong>Laboratorium:</strong> ' + cp.laboratory + '<br>';
        }

        // if (cp.collectionPointClassification) {
        //     content += '<strong>Klasyfikacja:</strong> ' + cp.collectionPointClassification + '<br>';
        // }

        // if (cp.collectionPointLocation) {
        //     content += '<strong>Rodzaj:</strong> ' + cp.collectionPointLocation + '<br>';
        // }

        // if (cp.collectionPointPartner) {
        //     content += '<strong>Partner:</strong> ' + cp.collectionPointPartner + '<br>';
        // }

        if (cp.user) {
            content += '<strong>Koordynator:</strong> ' + cp.user + '<br>';
        }

        if (cp.user2) {
            content += '<strong>Dyrektor regionalny:</strong> ' + cp.user2 + '<br>';
        }

        if (cp.collectionPointType) {
            content += '<strong>Typ:</strong> ' + cp.collectionPointType + '<br>';
        }

        if (cp.additionalInfo) {
            content += '<br>' + cp.additionalInfo + '<br>';
        }

        if (cp.periods) {
            content += '<br><table>';
            content += '<tr><th></th><th class="text-center">Godziny pracy</th><th>Godziny pobrań</th></tr>';

            for (let i = 1; i <= 7; i++) {
                content += '<tr><th class="text-right">';

                switch (i) {
                    case 1:
                        content += 'poniedziałek';
                        break;
                    case 2:
                        content += 'wtorek';
                        break;
                    case 3:
                        content += 'środa';
                        break;
                    case 4:
                        content += 'czwartek';
                        break;
                    case 5:
                        content += 'piątek';
                        break;
                    case 6:
                        content += 'sobota';
                        break;
                    case 7:
                        content += 'niedziela';
                        break;
                }

                content += '</th><td>';
                if (cp.periods['work'] && cp.periods['work'][i]) {
                    content += cp.periods['work'][i];
                }
                content += '</td><td>';
                if (cp.periods['collect'] && cp.periods['collect'][i]) {
                    content += cp.periods['collect'][i];
                }
                content += '</td></tr>';
            }

            content += '</table>';
        }

        return content;
    },

    propertiesToArray: function (obj) {
        const isObject = val => typeof val === 'object' && !Array.isArray(val);
        const addDelimiter = (a, b) => a ? `${a}.${b}` : b;
        const paths = (obj = {}, head = '') => {
            if (obj === null) {
                obj = {};
            }
            return Object.entries(obj).reduce((product, [key, value]) => {
                let fullPath = addDelimiter(head, key)
                return isObject(value) ? product.concat(paths(value, fullPath)) : (product.concat(value))
            }, []);
        }

        return paths(obj);
    },
};
