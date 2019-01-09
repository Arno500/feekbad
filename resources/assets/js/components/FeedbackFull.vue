<template>

  <div>
    <router-link :to="{ name: 'home'}">
      <return-button></return-button>
    </router-link>

    <keep-alive>
      <sui-card>
        <sui-card-content v-bind:class="{ finished: finished }">
          <sui-card-header>
            <sui-grid :columns="2" divided>
              <sui-grid-row>
                <sui-grid-column :width="1" :mobile="4" align="center" verticalAlign="middle">
                  <votes-counter :votes="votes"></votes-counter>
                  <!-- <sui-header size="large">{{ votes }}</sui-header> -->
                </sui-grid-column>
                <sui-grid-column :width="9" :mobile="10" verticalAlign="middle">
                  <sui-header size="large">
                    <div v-if="isEditing"><input ref="name" placeholder="Nom" class="feedback-edit" v-model="nameData" @keyup="debouncedUpdateName"></div>
                    <span v-else>{{ nameData }}</span>
                  </sui-header>
                  <sui-card-meta v-if="finished">
                    <span>Terminé</span>
                  </sui-card-meta>
                </sui-grid-column>
              </sui-grid-row>
            </sui-grid>
          </sui-card-header>
        </sui-card-content>
        <sui-card-content>
          <sui-header size="tiny">Description :</sui-header>
          <div class="ui feed">
            <textarea v-if="isEditing" v-model="descriptionData" @keydown="setTextareaSize" @keyup="debouncedUpdateDescription" class="feedback-edit" ref="description">
          </textarea>
            <p class="lb" v-else>{{ descriptionData }}</p>
          </div>
          <div v-if="this.$store.state.admin">
            <div is="sui-divider" class="admin-divider" horizontal>
              <h5 is="sui-header" class="admin-separation" fitted>
                <i class="edit icon"></i>
                Administration
              </h5>
            </div>
            <sui-button icon="check" label-position="left" positive content="Terminer" v-if="!finished" v-on:click="changeState" />
            <sui-button icon="redo" label-position="left" content="Rouvrir" v-else v-on:click="changeState" />
            <sui-button icon="pencil alternate" label-position="left" content="Modifier" v-on:click="edit" />
            <sui-button icon="delete" floated="right" negative label-position="left" v-on:click="showDeleteConfirm" content="Supprimer" />

            <div class="ui hidden divider"></div>

            <!--<sui-dropdown placeholder="Tags" fluid selection :options="options" v-model="current" />-->
          </div>
        </sui-card-content>
      </sui-card>
    </keep-alive>
  </div>

</template>

<script>
module.exports = {
  data: function() {
    return {
      isEditing: false,
      nameData: this.name,
      descriptionData: this.description
    };
  },
  props: ["votes", "name", "description", "finished"],
  created: function() {
    this.debouncedUpdateName = _.debounce(this.updateName, 500, {
      maxWait: 4000
    });
    this.debouncedUpdateDescription = _.debounce(this.updateDescription, 500, {
      maxWait: 4000
    });
  },
  methods: {
    changeState: function() {
      this.$store.dispatch("setDone", this.$parent.$attrs.id);
    },
    edit: function() {
      var $this = this;
      this.isEditing = !this.isEditing;
      this.$nextTick(function() {
        if ($this.isEditing) {
          $this.$refs.name.focus();
          var savedValue = $this.$refs.description.value;
          $this.$refs.description.value = "";
          $this.$refs.description.baseScrollHeight =
            $this.$refs.description.scrollHeight;
          $this.$refs.description.value = savedValue;
          $this.$refs.description.rows = 3;
          $this.setTextareaSize();
        }
      });
    },
    setTextareaSize: function() {
      //this.$refs.description.baseScrollHeight = this.$refs.description.scrollHeight;
      this.$refs.description.rows = 3;
      rows = Math.ceil(
        (this.$refs.description.scrollHeight -
          this.$refs.description.baseScrollHeight) /
          16
      );
      this.$refs.description.rows = 3 + rows;
    },
    updateName: function() {
      console.log("trigger Name", this.nameData);
      this.$store.dispatch("update", {
        id: this.$parent.$attrs.id,
        name: this.nameData,
        description: null
      });
    },
    updateDescription: function() {
      this.$store.dispatch("update", {
        id: this.$parent.$attrs.id,
        name: null,
        description: this.descriptionData
      });
    },
    showDeleteConfirm: function() {
      var $this = this;
      this.$toast.question(
        "Souhaitez-vous vraiment supprimer le feedback n°" +
          this.$parent.$attrs.id,
        "Confirmation :",
        {
          balloon: true,
          timeout: false,
          overlay: true,
          overlayClose: true,
          progressBar: false,
          toastOnce: true,
          position: "center",
          color: "red",
          drag: false,
          buttons: [
            [
              "<button><b>Oui</b></button>",
              function(instance, toast) {
                instance.hide({ transitionOut: "fadeOut" }, toast, "button");
                $this.deleteFeedback();
              }
            ],
            [
              "<button>Annuler</button>",
              function(instance, toast) {
                instance.hide({ transitionOut: "fadeOut" }, toast, "button");
              },
              true
            ]
          ]
        }
      );
    },
    deleteFeedback: function() {
      this.$store.dispatch("delete", this.$parent.$attrs.id);
      this.$router.push({ name: "home" });
    }
  }
};
</script>

<style scoped>
.flip-list-move {
  transition: transform 1s;
}

.card-link {
  display: block;
}
.ui.grid {
  margin: 0;
}
.ui.card {
  width: 100%;
  margin-top: 1rem;
  margin-bottom: 2rem;
}
.ui.card > .content:nth-of-type(2) {
  padding: 3rem;
}
p.lb {
  overflow-wrap: break-word;
}
</style>