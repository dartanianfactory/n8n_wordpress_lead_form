const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(bodyParser.json());

let leads = [];
let leadIdCounter = 1;

app.get('/api/leads', (req, res) => {
    res.json(leads);
});

app.post('/api/leads', (req, res) => {
    try {
        const { name, email, phone, message, source } = req.body;

        if (!name || !email || !phone) {
            return res.status(400).json({
                error: 'Missing required fields: name, email, phone'
            });
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return res.status(400).json({
                error: 'Invalid email format'
            });
        }

        const newLead = {
            id: leadIdCounter++,
            name,
            email,
            phone,
            message: message || '',
            source: source || 'website',
            status: 'new',
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };
        
        leads.push(newLead);
        
        console.log('New lead created:', newLead);
        
        res.status(201).json({
            success: true,
            data: newLead
        });
        
    } catch (error) {
        console.error('Error creating lead:', error);
        res.status(500).json({
            error: 'Internal server error'
        });
    }
});

app.put('/api/leads/:id', (req, res) => {
    try {
        const leadId = parseInt(req.params.id);
        const { status, notes } = req.body;
        
        const leadIndex = leads.findIndex(lead => lead.id === leadId);
        
        if (leadIndex === -1) {
            return res.status(404).json({
                error: 'Lead not found'
            });
        }

        if (status) {
            leads[leadIndex].status = status;
        }
        if (notes) {
            if (!leads[leadIndex].notes) {
                leads[leadIndex].notes = [];
            }
            leads[leadIndex].notes.push({
                content: notes,
                created_at: new Date().toISOString()
            });
        }
        
        leads[leadIndex].updated_at = new Date().toISOString();
        
        console.log('Lead updated:', leads[leadIndex]);
        
        res.json({
            success: true,
            data: leads[leadIndex]
        });
        
    } catch (error) {
        console.error('Error updating lead:', error);
        res.status(500).json({
            error: 'Internal server error'
        });
    }
});

app.get('/api/leads/:id', (req, res) => {
    const leadId = parseInt(req.params.id);
    const lead = leads.find(lead => lead.id === leadId);
    
    if (!lead) {
        return res.status(404).json({
            error: 'Lead not found'
        });
    }
    
    res.json(lead);
});

app.post('/api/webhook/lead-status', (req, res) => {
    console.log('Webhook received:', req.body);
    res.json({ received: true });
});

app.get('/health', (req, res) => {
    res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

app.listen(PORT, () => {
    console.log(`Mock API server running on port ${PORT}`);
    console.log('Available endpoints:');
    console.log('  GET  /api/leads');
    console.log('  POST /api/leads');
    console.log('  PUT  /api/leads/:id');
    console.log('  GET  /api/leads/:id');
    console.log('  POST /api/webhook/lead-status');
});
