<template>
  <transition appear>
    <div>

      <sui-dimmer v-bind:active="this.$store.state.loading">
        <sui-loader />
      </sui-dimmer>

      <div v-if="!this.$store.state.feedbacks || !this.$store.state.feedbacks.length">
        <div v-if="this.$store.state.admin">
          <h2>Il n'y aucun feedback... Créez-en un maintenant !</h2>
          <br>
        </div>
        <div v-else>
          <h2>Il n'y aucun feedback...
            <br>¯\_(ツ)_/¯<br> Allez voir Florence, Gaëlle ou Florian pour proposer vos idées d'améliorations !</h2>
        </div>
      </div>
      <feedback-creator v-if="this.$store.state.admin"></feedback-creator>

      <transition-group name="flip-list" tag="div">
        <feedback-card v-for="feedback in orderedFeedbacks" v-if="!feedback.done" v-bind:key="feedback.id" v-bind:name="feedback.name" v-bind:description="feedback.description" v-bind:votes="feedback.votes" v-bind:myVotes="feedback.myVotes"></feedback-card>
      </transition-group>
      <sui-divider horizontal v-if="this.$store.getters.isAnyDoneFeedback">Terminés</sui-divider>
      <transition-group name="flip-list" tag="div">
        <feedback-card v-for="feedback in orderedFeedbacks" v-if="feedback.done" class="done" v-bind:key="feedback.id" v-bind:name="feedback.name" v-bind:description="feedback.description" v-bind:votes="feedback.votes" v-bind:myVotes="feedback.myVotes" :finished="true"></feedback-card>
      </transition-group>
    </div>

  </transition>
</template>

<script>
export default {
  computed: {
    orderedFeedbacks: function() {
      return _.orderBy(
        this.$store.state.feedbacks,
        ["done", "votes", "id"],
        ["asc", "desc", "desc"]
      );
    }
  },
  mounted() {
    this.$store.dispatch("refresh");
  }
};
</script>

<style scoped>
h2 {
  text-align: center;
}
</style>
