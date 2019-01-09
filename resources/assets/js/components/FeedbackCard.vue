<template>
  <sui-grid :columns="2" divided>
    <sui-grid-row>
      <sui-grid-column :width="2" align="middle" verticalAlign="middle">
        <sui-icon v-if="this.$store.state.connected && !finished" name="angle up" class="voters" v-bind:class="{disabled: upvoteDisabled}" @click.self="upvote" />
        <div>
          <sui-header v-if="this.$store.state.connected && !finished" class="my-votes" size="small">{{ myVotes }}</sui-header>
          <sui-header class="votes-header" size="large">{{ votes }}</sui-header>
        </div>
        <sui-icon v-if="this.$store.state.connected && !finished" name="angle down" class="voters" v-bind:class="{disabled: downvoteDisabled}" @click.self="downvote" />
      </sui-grid-column>

      <sui-grid-column :width="14" verticalAlign="middle" class="feedback-card-text">
        <sui-header size="large">{{ name }}</sui-header>
        <p>{{ description | truncate(100) }}</p>
        <router-link class="card-link" :to="{ name: 'feedbackView', params: { id: this.$vnode.key }}">
        </router-link>
      </sui-grid-column>

    </sui-grid-row>
  </sui-grid>

</template>

<script>
module.exports = {
  data: function() {
    return {
      maxed: false,
      votesNumber: this.$store.state.feedbacks[
        this.$store.getters.getFeedbackIndex(this.$vnode.key)
      ].myVotes
    };
  },
  props: ["votes", "name", "description", "myVotes", "finished"],
  computed: {
    upvoteDisabled: function() {
      return !this.$store.getters.isUpvotable(this.$vnode.key);
    },
    downvoteDisabled: function() {
      return !this.$store.getters.isDownvotable(this.$vnode.key);
    }
  },
  methods: {
    upvote: function() {
      if (this.$store.getters.isUpvotable(this.$vnode.key)) {
        this.votesNumber++;
        this.$store.dispatch("vote", {
          id: this.$vnode.key,
          minus: false
        });
      }
    },
    downvote: function() {
      if (this.$store.getters.isDownvotable(this.$vnode.key)) {
        this.votesNumber--;
        this.$store.dispatch("vote", {
          id: this.$vnode.key,
          minus: true
        });
      }
    }
  }
};
</script>

<style scoped>
.flip-list-move {
  transition: transform 1s;
}

.card-link {
  position: absolute;
  display: block;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.grid {
  transition: 0.3s;
  pointer-events: painted;
}
.grid:hover {
  transition: 0.3s;
  background-color: rgb(238, 238, 238);
}
.ui.grid {
  margin: 0;
}

.ui.grid > .row {
  padding-top: 1.25rem;
  padding-bottom: 1.25rem;
}

.feedback-card-text .header {
  color: #4183c4;
  transition: all 0.4s;
}

.feedback-card-text p {
  color: #0000009c;
  transition: all 0.4s;
}

.ui.grid:hover p {
  color: black;
}

.ui.grid:hover .header {
  color: #325475;
}

.icon {
  padding: 0;
  margin: 0 auto;
  font-size: 2rem;
  color: black;
  width: 3rem;
  transition: all 0.4s;
}

.icon:hover {
  color: rgb(155, 155, 155);
}

.votes-header {
  margin-bottom: -0.7rem;
  margin-top: -0.2rem;
}
</style>