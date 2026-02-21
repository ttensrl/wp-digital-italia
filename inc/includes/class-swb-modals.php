<?php
/**
 * Class SWB_Modals
 *
 * Gestisce il rendering delle modali HTML
 *
 * @package SimpleWPBooking
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWB_Modals {

    /**
     * Renderizza tutte le modali necessarie per il calendario
     */
    public static function render_all_modals(): void
    {
        self::render_add_slot_modal();
        self::render_edit_slot_modal();
        self::render_generate_modal();
        self::render_delete_month_modal();
        self::render_delete_day_modal();
        self::render_delete_all_modal();
    }

    /**
     * Modale Aggiungi Slot Singolo
     */
    private static function render_add_slot_modal(): void
    {
        ?>
        <!-- Modale Aggiungi Slot -->
        <div id="swbAddSlotModal" class="swb-modal-overlay">
            <div class="swb-modal">
                <div class="swb-modal-header add-slot">
                    <h2>Aggiungi Slot</h2>
                    <button class="swb-modal-close" onclick="swbCloseAddSlotModal()">&times;</button>
                </div>
                <div class="swb-modal-body">
                    <form id="swbAddSlotForm">
                        <input type="hidden" id="swb_add_slot_date" name="add_slot_date">

                        <div class="swb-form-group">
                            <label for="swb_add_slot_date_display">Data</label>
                            <input type="text" id="swb_add_slot_date_display" readonly style="background: #f5f5f5; cursor: not-allowed;">
                        </div>

                        <div class="swb-form-group-inline">
                            <div class="swb-form-group">
                                <label for="swb_add_start_time">Orario Inizio</label>
                                <input type="time" id="swb_add_start_time" name="add_start_time" value="09:00" required>
                            </div>
                            <div class="swb-form-group">
                                <label for="swb_add_slot_duration">Durata (minuti)</label>
                                <input type="number" id="swb_add_slot_duration" name="add_slot_duration" value="45" min="15" max="240" step="15" required>
                            </div>
                        </div>

                        <div class="swb-form-group">
                            <label for="swb_add_max_bookings">Posti Disponibili</label>
                            <input type="number" id="swb_add_max_bookings" name="add_max_bookings" value="1" min="1" max="50" required>
                            <div class="swb-form-help">Numero massimo di prenotazioni per questo slot</div>
                        </div>
                    </form>
                </div>
                <div class="swb-modal-footer">
                    <button type="button" class="swb-btn swb-btn-secondary" onclick="swbCloseAddSlotModal()">Annulla</button>
                    <button type="button" class="swb-btn swb-btn-primary" onclick="swbSubmitAddSlot()">Aggiungi Slot</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Modale Modifica Slot
     */
    private static function render_edit_slot_modal(): void
    {
        ?>
        <!-- Modale Modifica Slot -->
        <div id="swbEditSlotModal" class="swb-modal-overlay">
            <div class="swb-modal">
                <div class="swb-modal-header edit-slot">
                    <h2>Modifica Slot</h2>
                    <button class="swb-modal-close" onclick="swbCloseEditSlotModal()">&times;</button>
                </div>
                <div class="swb-modal-body">
                    <form id="swbEditSlotForm">
                        <input type="hidden" id="swb_edit_slot_id" name="edit_slot_id">

                        <div class="swb-form-group">
                            <label for="swb_edit_slot_date_display">Data</label>
                            <input type="text" id="swb_edit_slot_date_display" readonly style="background: #f5f5f5; cursor: not-allowed;">
                        </div>

                        <div class="swb-form-group-inline">
                            <div class="swb-form-group">
                                <label for="swb_edit_start_time">Orario Inizio</label>
                                <input type="time" id="swb_edit_start_time" name="edit_start_time" required>
                            </div>
                            <div class="swb-form-group">
                                <label for="swb_edit_slot_duration">Durata (minuti)</label>
                                <input type="number" id="swb_edit_slot_duration" name="edit_slot_duration" min="15" max="240" step="15" required>
                            </div>
                        </div>

                        <div class="swb-form-group">
                            <label for="swb_edit_max_bookings">Posti Disponibili</label>
                            <input type="number" id="swb_edit_max_bookings" name="edit_max_bookings" min="1" max="50" required>
                            <div class="swb-form-help">Numero massimo di prenotazioni per questo slot</div>
                        </div>

                        <div class="swb-form-group" id="swb_edit_bookings_info" style="background: #fff3cd; padding: 12px; border-radius: 4px; border: 1px solid #ffc107;">
                            <p style="margin: 0; font-size: 13px; color: #856404;">
                                <strong>Prenotazioni attuali: <span id="swb_current_bookings">0</span></strong><br>
                                <small>Attenzione: riducendo i posti disponibili sotto al numero di prenotazioni attuali, lo slot risulterà sovra-prenotato.</small>
                            </p>
                            <!-- Link per visualizzare le prenotazioni: mostrato sotto le informazioni sulle prenotazioni -->
                            <div style="margin-top: 8px;">
                                <a id="swb_view_slot_bookings_link" href="#" target="_blank" rel="noopener noreferrer" style="display: none; font-weight: 600; color: #0073aa;">Visualizza prenotazioni</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="swb-modal-footer" style="justify-content: space-between;">
                    <button type="button" class="swb-btn" onclick="swbDeleteSlotFromModal()" style="background: #d63638; color: white;">
                        Elimina Slot
                    </button>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="swb-btn swb-btn-secondary" onclick="swbCloseEditSlotModal()">Annulla</button>
                        <button type="button" class="swb-btn swb-btn-primary" onclick="swbSubmitEditSlot()">Salva Modifiche</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Modale Genera Slot
     */
    private static function render_generate_modal(): void
    {
        ?>
        <!-- Modale Genera Slot -->
        <div id="swbGenerateModal" class="swb-modal-overlay">
            <div class="swb-modal">
                <div class="swb-modal-header generate">
                    <h2 id="swbModalTitle">Genera Slot</h2>
                    <button class="swb-modal-close" onclick="swbCloseModal()">&times;</button>
                </div>
                <div class="swb-modal-body">
                    <form id="swbGenerateForm">
                        <input type="hidden" id="swb_generate_type" name="generate_type">
                        <input type="hidden" id="swb_week_start" name="week_start">

                        <div class="swb-form-group" id="swb_week_start_group" style="display: none;">
                            <label for="swb_week_start_input">Data Inizio Settimana</label>
                            <input type="date" id="swb_week_start_input" class="swb-form-control">
                        </div>

                        <div class="swb-form-group-inline">
                            <div class="swb-form-group">
                                <label for="swb_start_time">Orario Inizio</label>
                                <input type="time" id="swb_start_time" name="start_time" value="09:00" required>
                            </div>
                            <div class="swb-form-group">
                                <label for="swb_end_time">Orario Fine</label>
                                <input type="time" id="swb_end_time" name="end_time" value="17:00" required>
                            </div>
                        </div>

                        <div class="swb-form-group-inline">
                            <div class="swb-form-group">
                                <label for="swb_slot_duration">Durata Slot (minuti)</label>
                                <input type="number" id="swb_slot_duration" name="slot_duration" value="45" min="15" max="120" step="15" required>
                                <div class="swb-form-help">Durata di ogni singolo slot</div>
                            </div>
                            <div class="swb-form-group">
                                <label for="swb_max_bookings">Posti per Slot</label>
                                <input type="number" id="swb_max_bookings" name="max_bookings" value="1" min="1" max="50" required>
                                <div class="swb-form-help">Prenotazioni consentite</div>
                            </div>
                        </div>

                        <div class="swb-form-group">
                            <label for="swb_slot_interval">Intervallo tra slot (minuti)</label>
                            <input type="number" id="swb_slot_interval" name="slot_interval" value="0" min="0" max="120" step="5">
                            <div class="swb-form-help">Spazio libero tra la fine di uno slot e l'inizio del successivo (default 0)</div>
                        </div>

                        <div class="swb-form-group">
                            <div class="swb-checkbox-wrapper" onclick="document.getElementById('swb_enable_break').click()">
                                <input type="checkbox" id="swb_enable_break" name="enable_break" onclick="event.stopPropagation(); swbToggleBreak()">
                                <label for="swb_enable_break" style="margin: 0; cursor: pointer; font-weight: 600;">Pausa Pranzo</label>
                            </div>
                            <div id="swb_break_fields" class="swb-break-fields">
                                <div class="swb-form-group-inline">
                                    <div class="swb-form-group">
                                        <label for="swb_break_start">Dalle</label>
                                        <input type="time" id="swb_break_start" name="break_start" value="12:00">
                                    </div>
                                    <div class="swb-form-group">
                                        <label for="swb_break_end">Alle</label>
                                        <input type="time" id="swb_break_end" name="break_end" value="14:00">
                                    </div>
                                </div>
                                <div class="swb-form-help">Durante la pausa non verranno generati slot</div>
                            </div>
                        </div>

                        <!-- Orari Personalizzati per Giorno -->
                        <div class="swb-form-group" id="swb_custom_hours_section" style="display: none;">
                            <div class="swb-checkbox-wrapper" onclick="document.getElementById('swb_enable_custom_hours').click()">
                                <input type="checkbox" id="swb_enable_custom_hours" name="enable_custom_hours" onclick="event.stopPropagation(); swbToggleCustomHours()">
                                <label for="swb_enable_custom_hours" style="margin: 0; cursor: pointer; font-weight: 600;">⚙️ Orari Personalizzati per Giorno</label>
                            </div>
                            <div id="swb_custom_hours_fields" class="swb-custom-hours-fields">
                                <div class="swb-form-help" style="margin-bottom: 15px;">Lascia vuoto per usare gli orari di default. Esempio: alcuni giorni solo pomeriggio.</div>

                                <!-- Lunedì -->
                                <div class="swb-day-hours">
                                    <strong>Lunedì</strong>
                                    <div class="swb-form-group-inline" style="margin-top: 8px;">
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_mon_start" name="mon_start" placeholder="Inizio">
                                        </div>
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_mon_end" name="mon_end" placeholder="Fine">
                                        </div>
                                    </div>
                                </div>

                                <!-- Martedì -->
                                <div class="swb-day-hours">
                                    <strong>Martedì</strong>
                                    <div class="swb-form-group-inline" style="margin-top: 8px;">
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_tue_start" name="tue_start" placeholder="Inizio">
                                        </div>
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_tue_end" name="tue_end" placeholder="Fine">
                                        </div>
                                    </div>
                                </div>

                                <!-- Mercoledì -->
                                <div class="swb-day-hours">
                                    <strong>Mercoledì</strong>
                                    <div class="swb-form-group-inline" style="margin-top: 8px;">
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_wed_start" name="wed_start" placeholder="Inizio">
                                        </div>
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_wed_end" name="wed_end" placeholder="Fine">
                                        </div>
                                    </div>
                                </div>

                                <!-- Giovedì -->
                                <div class="swb-day-hours">
                                    <strong>Giovedì</strong>
                                    <div class="swb-form-group-inline" style="margin-top: 8px;">
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_thu_start" name="thu_start" placeholder="Inizio">
                                        </div>
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_thu_end" name="thu_end" placeholder="Fine">
                                        </div>
                                    </div>
                                </div>

                                <!-- Venerdì -->
                                <div class="swb-day-hours">
                                    <strong>Venerdì</strong>
                                    <div class="swb-form-group-inline" style="margin-top: 8px;">
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_fri_start" name="fri_start" placeholder="Inizio">
                                        </div>
                                        <div class="swb-form-group">
                                            <input type="time" id="swb_fri_end" name="fri_end" placeholder="Fine">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="swb-modal-footer">
                    <button type="button" class="swb-btn swb-btn-secondary" onclick="swbCloseModal()">Annulla</button>
                    <button type="button" class="swb-btn swb-btn-primary" onclick="swbSubmitGenerate()">Genera Slot</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Modale Conferma Elimina Tutti del Mese
     */
    private static function render_delete_month_modal(): void
    {
        ?>
        <!-- Modale Conferma Elimina Tutti del Mese -->
        <div id="swbDeleteMonthModal" class="swb-modal-overlay">
            <div class="swb-modal" style="max-width: 500px;">
                <div class="swb-modal-header" style="background: #d63638;">
                    <h2>⚠️ Elimina Slot del Mese</h2>
                    <button class="swb-modal-close" onclick="swbCloseDeleteMonthModal()" style="color: white;">&times;</button>
                </div>
                <div class="swb-modal-body">
                    <p style="font-size: 15px; line-height: 1.6; margin-bottom: 15px;">
                        <strong>ATTENZIONE!</strong> Stai per eliminare <strong>TUTTI gli slot del mese corrente</strong>.
                    </p>
                    <p style="font-size: 14px; color: #856404; background: #fff3cd; padding: 12px; border-radius: 4px; border: 1px solid #ffc107;">
                        Questa azione eliminerà anche tutte le prenotazioni associate e <strong>NON può essere annullata</strong>!
                    </p>
                </div>
                <div class="swb-modal-footer">
                    <button type="button" class="swb-btn swb-btn-secondary" onclick="swbCloseDeleteMonthModal()">Annulla</button>
                    <button type="button" class="swb-btn" onclick="swbConfirmDeleteMonth()" style="background: #d63638; color: white;">Elimina Tutti</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Modale Conferma Elimina Slot del Giorno
     */
    private static function render_delete_day_modal(): void
    {
        ?>
        <!-- Modale Conferma Elimina Slot del Giorno -->
        <div id="swbDeleteDayModal" class="swb-modal-overlay">
            <div class="swb-modal" style="max-width: 500px;">
                <div class="swb-modal-header" style="background: #d63638;">
                    <h2>⚠️ Elimina Slot del Giorno</h2>
                    <button class="swb-modal-close" onclick="swbCloseDeleteDayModal()" style="color: white;">&times;</button>
                </div>
                <div class="swb-modal-body">
                    <p style="font-size: 15px; line-height: 1.6; margin-bottom: 15px;">
                        <strong>ATTENZIONE!</strong> Stai per eliminare <strong>TUTTI gli slot del giorno:</strong>
                    </p>
                    <p id="swbDeleteDayDate" style="font-size: 16px; font-weight: 600; text-align: center; margin: 15px 0; padding: 12px; background: #f5f5f5; border-radius: 4px;"></p>
                    <p style="font-size: 14px; color: #856404; background: #fff3cd; padding: 12px; border-radius: 4px; border: 1px solid #ffc107;">
                        Questa azione eliminerà anche tutte le prenotazioni associate e <strong>NON può essere annullata</strong>!
                    </p>
                </div>
                <div class="swb-modal-footer">
                    <button type="button" class="swb-btn swb-btn-secondary" onclick="swbCloseDeleteDayModal()">Annulla</button>
                    <button type="button" class="swb-btn" onclick="swbConfirmDeleteDay()" style="background: #d63638; color: white;">Elimina Tutti</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Modale Conferma Elimina TUTTI gli Slot del Servizio
     */
    private static function render_delete_all_modal(): void
    {
        ?>
        <!-- Modale Conferma Elimina TUTTI gli Slot del Servizio -->
        <div id="swbDeleteAllModal" class="swb-modal-overlay">
            <div class="swb-modal" style="max-width: 550px;">
                <div class="swb-modal-header" style="background: #d63638;">
                    <h2>🚨 ELIMINAZIONE TOTALE</h2>
                    <button class="swb-modal-close" onclick="swbCloseDeleteAllModal()" style="color: white;">&times;</button>
                </div>
                <div class="swb-modal-body">
                    <p style="font-size: 16px; font-weight: 600; color: #d63638; margin-bottom: 15px; text-align: center;">
                        ATTENZIONE MASSIMA!
                    </p>
                    <p style="font-size: 15px; line-height: 1.6; margin-bottom: 15px;">
                        Stai per eliminare <strong>TUTTI gli slot di questo servizio</strong>, SENZA LIMITI TEMPORALI!
                    </p>
                    <div style="background: #ffebee; border: 2px solid #d63638; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600;">Questa azione eliminerà:</p>
                        <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8;">
                            <li>Tutti gli slot passati</li>
                            <li>Tutti gli slot presenti</li>
                            <li>Tutti gli slot futuri</li>
                            <li>Tutte le prenotazioni associate</li>
                        </ul>
                    </div>
                    <p style="font-size: 14px; color: #d63638; text-align: center; font-weight: 600;">
                        ⚠️ Questa azione è IRREVERSIBILE ⚠️
                    </p>
                    <div class="swb-form-group" style="margin-top: 20px;">
                        <label for="swbDeleteAllConfirm" style="font-weight: 600; margin-bottom: 8px; display: block;">
                            Per confermare, digita: <span style="color: #d63638;">ELIMINA TUTTO</span>
                        </label>
                        <input type="text" id="swbDeleteAllConfirm" class="swb-form-control" placeholder="Digita qui..." style="text-align: center; font-weight: 600; width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 4px;">
                    </div>
                </div>
                <div class="swb-modal-footer">
                    <button type="button" class="swb-btn swb-btn-secondary" onclick="swbCloseDeleteAllModal()">Annulla</button>
                    <button type="button" class="swb-btn" onclick="swbConfirmDeleteAll()" style="background: #d63638; color: white;">Elimina Tutto</button>
                </div>
            </div>
        </div>
        <?php
    }
}

