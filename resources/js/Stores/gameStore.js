import { defineStore } from 'pinia';

export const useGameStore = defineStore('game', {
    state: () => ({
        question: null,
        players: [],
        winner: null,
        currentUser: null,
        remainingSeconds: 0,
        timerInterval: null,
    }),

    getters: {
        isAdmin: (state) => state.currentUser?.role === 'admin',
        isBanned: (state) => state.currentUser?.banned,
        isMyTurn: (state) => {
            return state.question?.status === 'buzzed' &&
                state.question?.buzzed_user_id === state.currentUser?.id;
        },
        hasAnswered: (state) => !!state.question?.answer
    },

    actions: {
        // hydratation -> inizializza le props ed avvia/sincronizza il timer
        initGame(props) {
            this.question = props.question;
            this.players = props.players;
            this.winner = props.game_winner;
            this.currentUser = props.auth.user;

            this.syncTimer();
        },

        // gestione dispatch del socket -> aggiorna lo stato
        handleSocketEvent(payload) {
            console.log('handleSocketEvent', payload);

            this.question = payload.question;
            this.players = payload.players;
            this.winner = payload.game_winner;

            // recupera i dati del current user dal payload dell'evento -> dati aggiornati e corretti per l'Header
            if (this.currentUser && !this.isAdmin) {
                const userData = this.players.find(p => p.id === this.currentUser.id);
                if (userData) {
                    this.currentUser.banned = userData.banned;
                    this.currentUser.score = userData.score;
                }
            }

            this.syncTimer();
        },

        // lancia funzione temporizzata che ricalcola il timer in modo che sia sincrono con quanto presente su db
        // ogni volta che viene chiamata rigenera il timer con i dati sicuramente corretti
        syncTimer() {
            clearInterval(this.timerInterval);

            if (this.question && ['active', 'buzzed'].includes(this.question.status)) {
                const update = () => {
                    const end = new Date(this.question.timer_ends_at).getTime();
                    const now = new Date().getTime();
                    this.remainingSeconds = Math.max(0, Math.ceil((end - now) / 1000));
                    if (this.remainingSeconds <= 0) {
                        clearInterval(this.timerInterval);
                    }
                };

                update();
                this.timerInterval = setInterval(update, 100);
            } else {

                this.remainingSeconds = 0;
            }
        }
    }
});