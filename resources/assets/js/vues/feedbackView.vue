<template>
  <transition appear>
    <div>
      <sui-dimmer v-bind:active="this.$store.state.loading">
        <sui-loader />
      </sui-dimmer>
      <transition tag="div">
        <feedback-full v-bind:name="currentFeedback.name" v-bind:description="currentFeedback.description" v-bind:votes="currentFeedback.votes" v-bind:finished="currentFeedback.done" v-bind:key="currentFeedback.id"></feedback-full>
      </transition>
    </div>
  </transition>
</template>

<script>
export default {
  //props: ["active"],
  data: function() {
    return {
      //    feedback: {
      //      id: 0,
      //      votes: "???",
      //      name: "Chargement",
      //      description: "Merci de patienter un instant"
    };
  },
  //watch: {
  //  feedbacks: function() {
  //    this.updateFeedback();
  //  }
  //},
  computed: {
    currentFeedback: function() {
      if (this.$route.params.id) {
        var result = this.$store.getters.getFeedbackById(this.$route.params.id);
        if (typeof result !== "undefined" && !result) {
          var result = {
            id: 0,
            name: "Le feedback sélectionné n'existe pas",
            description: "Notez que le feedback peut avoir été supprimé"
          };
        }
        return result;
      } else {
        console.error("Pas d'ID donné !");
        return {
          id: 0,
          name: "Merci de fournir un ID de feedback valide !"
        };
      }
    }
  },
  mounted() {
    //var $this = this;
    //$this.getNewestFeedbacks();
    //setInterval(function() {
    //  $this.getNewestFeedbacks();
    //}, 30 * 1000);
  }
};
</script>