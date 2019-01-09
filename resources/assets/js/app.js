/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");
window.io = require("socket.io-client");
import SuiVue from "semantic-ui-vue";
import VueRouter from "vue-router";
import VueIziToast from "vue-izitoast";
import Vuex from "vuex";
import Echo from "laravel-echo";
//import "./registerServiceWorker";

//import Velocity from 'velocityjs';
var VueTruncate = require("vue-truncate-filter");

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));

Vue.use(Vuex);
Vue.use(SuiVue);
Vue.use(VueTruncate);
Vue.use(VueRouter);
Vue.use(VueIziToast, {
    imageWidth: 50,
    maxWidth: null,
    zindex: null,
    layout: 1,
    balloon: false,
    close: true,
    closeOnEscape: true,
    closeOnClick: false,
    displayMode: 0, // once, replace
    position: "bottomRight", // bottomRight, bottomLeft, topRight, topLeft, topCenter, bottomCenter, center
    target: "",
    targetFirst: true,
    timeout: 7000,
    rtl: false,
    animateInside: true,
    drag: true,
    pauseOnHover: true,
    resetOnHover: true,
    progressBar: true,
    progressBarColor: "",
    progressBarEasing: "linear",
    overlay: false,
    overlayClose: false,
    overlayColor: "rgba(0, 0, 0, 0.6)",
    transitionIn: "fadeInUp",
    transitionOut: "fadeOut",
    transitionInMobile: "fadeInUp",
    transitionOutMobile: "fadeOutDown"
});
Vue.component("parent", require("./vues/parent.vue"));
Vue.component("votes-counter", require("./components/FeedbackVote.vue"));
Vue.component("feedback-card", require("./components/FeedbackCard.vue"));
Vue.component("return-button", require("./components/ReturnButton.vue"));
Vue.component("feedback-full", require("./components/FeedbackFull.vue"));
Vue.component(
    "feedback-creator-button",
    require("./components/FeedbackCreatorButton.vue")
);
Vue.component(
    "feedback-creator-form",
    require("./components/FeedbackCreatorForm.vue")
);
Vue.component("feedback-creator", require("./components/FeedbackCreator.vue"));
const home = require("./vues/home.vue");
const feedbackView = require("./vues/feedbackView.vue");

const routes = [
    {
        path: "/",
        name: "home",
        component: home,
        props: true
    },
    {
        path: "/feedback/:id",
        component: feedbackView,
        name: "feedbackView",
        props: true
    }
    //{ path: '/feedback', component: feedback }
];

function updateVote(elm) {
    axios.patch("api/feedbacks/" + elm.id, {
        votes: elm.votes
    });
}

const updateVoteDebounced = _.debounce(updateVote, 400, { leading: false });

const router = new VueRouter({
    routes, // short for `routes: routes`
    scrollBehavior(to, from, savedPosition) {
        return { x: 0, y: 0 };
    }
});

const store = new Vuex.Store({
    state: {
        feedbacks: [],
        admin: false,
        connected: false,
        refreshRunning: false,
        sendingRunning: false,
        parallelSendingRunning: 0,
        lastUpdated: 0,
        loading: true,
        toast: null,
        remainingVotes: 0
    },
    getters: {
        feedbacks: state => {
            return state.feedbacks;
        },
        getFeedbackById: function(state) {
            return function(id) {
                var feedbackResult;
                feedbackResult = state.feedbacks.find(
                    feedback => feedback.id === parseInt(id, 10)
                );
                if (!feedbackResult) {
                    feedbackResult = false;
                }
                return feedbackResult;
            };
        },
        getFeedbackIndex: function(state) {
            return function(id) {
                var feedbackResult;
                feedbackResult = state.feedbacks.findIndex(
                    feedback => feedback.id === parseInt(id, 10)
                );
                if (typeof feedbackResult == "undefined") {
                    feedbackResult = false;
                }
                return feedbackResult;
            };
        },
        isAnyDoneFeedback: function(state) {
            var feedbackResult = state.feedbacks.findIndex(
                feedback => feedback.done == true
            );
            if (feedbackResult == -1) {
                feedbackResult = false;
            } else {
                feedbackResult = true;
            }
            return feedbackResult;
        },
        isUpvotable: function(state) {
            return function(id) {
                var currentFeedbackId = this.getFeedbackIndex(id);
                if (state.feedbacks[currentFeedbackId].myVotes < 3) {
                    return true;
                } else {
                    return false;
                }
            };
        },
        isDownvotable: function(state) {
            return function(id) {
                var currentFeedbackId = this.getFeedbackIndex(id);
                if (state.feedbacks[currentFeedbackId].myVotes >= 1) {
                    return true;
                } else {
                    return false;
                }
            };
        }
    },
    mutations: {
        changeState(state, id) {
            var feedback = this.getters.getFeedbackIndex(id);
            state.feedbacks[feedback].done = !state.feedbacks[feedback].done;
        },
        mergeFeedbacks(state, array) {
            var currentFeedbackArray = state.feedbacks;
            var data;
            if (currentFeedbackArray && array.length) {
                data = _({}) // Start with an empty object
                    .merge(
                        _(currentFeedbackArray)
                            .groupBy("id")
                            .value(),
                        _(array)
                            .groupBy("id")
                            .value()
                    )
                    .values()
                    .flatten()
                    .value();
            } else if (!currentFeedbackArray && !array.length) {
                console.log("Empty !");
                //$this.active = false;
            } else if (array.length) {
                data = array;
            } else {
                return;
            }
            state.feedbacks = data;
            state.remainingVotes =
                9 -
                _.sumBy(data, function(elm) {
                    if (elm.done === 0) {
                        return elm.myVotes;
                    }
                });
        },
        updateFeedbackName(state, id, name) {
            state.feedbacks[id].name = name;
        },
        updateFeedbackDescription(state, id, description) {
            state.feedbacks[id].description = description;
        },
        updateVotePlus(state, elm) {
            updateVoteDebounced({
                id: elm.idOrig,
                votes: state.feedbacks[elm.id].myVotes + elm.vote
            });
            state.feedbacks[elm.id].myVotes += elm.vote;
            state.feedbacks[elm.id].votes += elm.vote;
            state.remainingVotes--;
        },
        updateVoteMinus(state, elm) {
            updateVoteDebounced({
                id: elm.idOrig,
                votes: state.feedbacks[elm.id].myVotes - elm.vote
            });
            state.feedbacks[elm.id].myVotes -= elm.vote;
            state.feedbacks[elm.id].votes -= elm.vote;
            state.remainingVotes++;
        },
        deleteFeedback(state, id) {
            state.feedbacks.splice(id, 1);
        }
    },
    actions: {
        refresh({ commit, state }) {
            var $this = this;
            if (!state.refreshRunning) {
                state.refreshRunning = true;
                axios
                    .get("api/feedbacks/get", {
                        params: {
                            lastrefreshtime: state.lastUpdated
                        }
                    })

                    .then(function(response) {
                        //console.log($this);

                        state.lastUpdated = Date.now();
                        state.refreshRunning = false;
                        state.loading = false;

                        if (state.toast) {
                            state.toast = false;
                            $this._vm.$root.$toast.destroy();
                        }

                        commit("mergeFeedbacks", response.data);

                        //$this.$parent.feedbacks = data;

                        //$this.$set(app, "feedbacks", data);
                        //app.$emit("feedbackupdated");
                    })

                    .catch(function(error) {
                        console.error(
                            "Cannot download latest data. Be sure to be online.",
                            error
                        );
                        state.refreshRunning = false;
                        if (error.response.status === 429) {
                            $this._vm.$root.$toast.error(
                                "Holà ! Merci de patienter une minute, manant !",
                                "Trop de requêtes",
                                { timeout: 10000 }
                            );
                        } else {
                            if (!state.toast) {
                                state.toast = true;
                                $this._vm.$root.$toast.error(
                                    "Impossible de récupérer les derniers feedbacks",
                                    "Erreur",
                                    {
                                        timeout: false,
                                        close: true,
                                        closeOnEscape: false,
                                        closeOnClick: false
                                    }
                                );
                            }
                        }
                    });
            } else {
                return;
            }
        },
        setDone({ commit, state, getters }, id) {
            var i = true;
            if (state.parallelSendingRunning <= 3) {
                state.parallelSendingRunning++;
                while (!state.sendingRunning && i) {
                    i = false;
                    commit("changeState", id);
                    axios
                        .patch("api/feedbacks/" + id, {
                            done:
                                state.feedbacks[getters.getFeedbackIndex(id)]
                                    .done
                        })
                        .then(function() {
                            state.parallelSendingRunning--;
                        })
                        .catch(function() {
                            state.parallelSendingRunning--;
                        });
                }
            }
        },
        update({ getters }, elm) {
            var currentFeedbackId = getters.getFeedbackIndex(elm.id);

            axios.patch("api/feedbacks/" + elm.id, {
                name: elm.name,
                description: elm.description
            });
        },
        vote({ state, commit, getters }, elm) {
            var currentFeedbackId = getters.getFeedbackIndex(elm.id);
            var currentFeedback = state.feedbacks[currentFeedbackId];

            if (elm.minus) {
                commit("updateVoteMinus", {
                    id: currentFeedbackId,
                    vote: 1,
                    idOrig: elm.id
                });
            } else {
                commit("updateVotePlus", {
                    id: currentFeedbackId,
                    vote: 1,
                    idOrig: elm.id
                });
            }
        },
        delete({ state, commit, getters }, id) {
            axios
                .delete("api/feedbacks/" + id)
                .then(function() {
                    commit("deleteFeedback", getters.getFeedbackIndex(id));
                })
                .catch(function(error) {
                    console.error("Error: ", error);
                });
        }
    }
});

var socket = new Echo({
    broadcaster: "socket.io",
    host: window.location.hostname + ":6001"
});

window.onload = function() {
    var app = new Vue({
        router,
        store
    }).$mount("#app");

    if (!app.$store.state.feedbacks.length) {
        app.$store.dispatch("refresh");
    }

    socket.channel("fbupdate").listen("FeedbacksUpdatedEvent", function(e) {
        app.$store.commit("mergeFeedbacks", [e.feedback]);
    });
    /* setInterval(function() {
        app.$store.dispatch("refresh");
    }, 15 * 1000); */
    if (admin) {
        app.$store.state.admin = true;
    } else {
        app.$store.state.admin = false;
    }
    if (connected) {
        app.$store.state.connected = true;
    } else {
        app.$store.state.connected = false;
    }
};
