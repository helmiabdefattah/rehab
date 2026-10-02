import { $ } from '../lib/dom.js';
import { WorkoutSession } from '../workout/session.js';

export function initTrain() {
    const data = document.getElementById('workout-data');
    const start = $('[data-start-workout]');
    if (!data || !start) return;

    let exercises = [];
    try {
        exercises = JSON.parse(data.textContent);
    } catch {
        return;
    }
    if (!exercises.length) return;

    start.addEventListener('click', () => {
        new WorkoutSession(exercises, {
            split: data.dataset.split,
            label: data.dataset.label,
        }).open();
    });
}
