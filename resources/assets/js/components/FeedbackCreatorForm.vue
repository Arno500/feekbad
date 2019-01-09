<template>
  <sui-form>
    <sui-form-field>
      <label>Titre du feedback :</label>
      <input v-model="name" v-on:keydown.enter="sendFeedback" placeholder="Titre">
    </sui-form-field>
    <sui-form-field>
      <label>Description :</label>
      <textarea v-model="description" rows="2" placeholder="Description"></textarea>
    </sui-form-field>
    <div is="sui-button-group" class="right floated">
      <sui-button v-on:click="$emit('close-editor')">Annuler</sui-button>
      <sui-button-or text="ou" />
      <sui-button primary v-on:click="sendFeedback" v-bind:loading="loading">Enregistrer</sui-button>
    </div>
  </sui-form>
</template>

<script>
import saveState from "vue-save-state";
export default {
  data: function() {
    return {
      name: null,
      description: null,
      sending: false,
      loading: false
    };
  },
  mixins: [saveState],
  methods: {
    getSaveStateConfig() {
      return {
        cacheKey: "feedback-form",
        saveProperties: ["name", "description"]
      };
    },
    sendFeedback: function() {
      if (!this.sending) {
        this.sending = true;
        this.loading = true;
        var $this = this;
        if (!this.name || !this.description) {
          $this.$root.$toast.error("Merci de remplir tous les champs !");
          return;
        }
        axios
          .put("api/feedbacks/", {
            name: this.name,
            description: this.description
          })
          .then(function() {
            $this.name = "";
            $this.description = "";
            $this.sending = false;
            $this.loading = false;
            Vue.nextTick(function() {
              $this.$emit("feedback-sent");
              $this.$root.$toast.success(
                "Le feedback est enregistré !",
                "Ouais !"
              );
              $this.$store.dispatch("refresh");
            });
          })
          .catch(function(error) {
            $this.sending = false;
            $this.loading = false;
            if (error.response.status === 401) {
              $this.$root.$toast.error(
                "Merci de changer de compte ou vous reconnecter",
                "Vous n'êtes pas autorisé à créer un feedback !"
              );
              console.error("Unauthorized", error);
            } else {
              $this.$root.$toast.error(
                "Une erreur est survenue : " + error,
                "Erreur"
              );
              console.error("Cannot send the feedback. Retry ?", error);
              console.dir(error);
            }
          });
      }
    }
  }
};
</script>

