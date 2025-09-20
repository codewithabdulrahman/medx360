import React from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Paper,
  Button,
  Chip,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  Divider,
} from '@mui/material';
import {
  Check as CheckIcon,
  Close as CloseIcon,
  Star as StarIcon,
  StarBorder as StarBorderIcon,
  Upgrade as UpgradeIcon,
} from '@mui/icons-material';

const LiteVsPremium: React.FC = () => {
  const features = [
    {
      category: 'Core Features',
      items: [
        { name: 'Appointment Booking', lite: true, premium: true },
        { name: 'Patient Management', lite: true, premium: true },
        { name: 'Provider Scheduling', lite: true, premium: true },
        { name: 'Basic Calendar View', lite: true, premium: true },
        { name: 'Email Notifications', lite: true, premium: true },
        { name: 'Basic Reporting', lite: true, premium: true },
      ]
    },
    {
      category: 'Advanced Features',
      items: [
        { name: 'Advanced Calendar Views', lite: false, premium: true },
        { name: 'Custom Fields Builder', lite: false, premium: true },
        { name: 'SMS Notifications', lite: false, premium: true },
        { name: 'WhatsApp Integration', lite: false, premium: true },
        { name: 'Payment Gateway Integration', lite: false, premium: true },
        { name: 'Advanced Reporting & Analytics', lite: false, premium: true },
      ]
    },
    {
      category: 'Customization',
      items: [
        { name: 'Basic Branding', lite: true, premium: true },
        { name: 'Custom Themes', lite: false, premium: true },
        { name: 'White-label Solution', lite: false, premium: true },
        { name: 'Custom Domain', lite: false, premium: true },
        { name: 'Advanced Form Builder', lite: false, premium: true },
        { name: 'API Access', lite: false, premium: true },
      ]
    },
    {
      category: 'Support & Security',
      items: [
        { name: 'Email Support', lite: true, premium: true },
        { name: 'Priority Support', lite: false, premium: true },
        { name: 'Phone Support', lite: false, premium: true },
        { name: 'HIPAA Compliance', lite: false, premium: true },
        { name: 'Advanced Security Features', lite: false, premium: true },
        { name: 'Data Backup & Recovery', lite: false, premium: true },
      ]
    },
    {
      category: 'Integrations',
      items: [
        { name: 'Google Calendar Sync', lite: true, premium: true },
        { name: 'Outlook Integration', lite: false, premium: true },
        { name: 'Telehealth Platforms', lite: false, premium: true },
        { name: 'EHR System Integration', lite: false, premium: true },
        { name: 'Third-party Apps', lite: false, premium: true },
        { name: 'Webhook Support', lite: false, premium: true },
      ]
    },
  ];

  const pricingPlans = [
    {
      name: 'Lite',
      price: 'Free',
      period: 'forever',
      description: 'Perfect for small practices getting started',
      features: [
        'Up to 100 appointments/month',
        'Basic patient management',
        'Email notifications',
        'Standard support',
      ],
      limitations: [
        'Limited customization options',
        'Basic reporting only',
        'No SMS notifications',
        'No payment integration',
      ],
      buttonText: 'Current Plan',
      buttonVariant: 'outlined' as const,
      popular: false,
    },
    {
      name: 'Premium',
      price: '$99',
      period: 'per month',
      description: 'Advanced features for growing practices',
      features: [
        'Unlimited appointments',
        'Advanced patient management',
        'SMS & WhatsApp notifications',
        'Payment gateway integration',
        'Custom fields builder',
        'Advanced reporting',
        'Priority support',
        'HIPAA compliance',
      ],
      limitations: [],
      buttonText: 'Upgrade Now',
      buttonVariant: 'contained' as const,
      popular: true,
    },
  ];

  const renderFeatureIcon = (available: boolean) => {
    return available ? (
      <CheckIcon color="success" />
    ) : (
      <CloseIcon color="disabled" />
    );
  };

  return (
    <Box>
      <Box textAlign="center" mb={4}>
        <Typography variant="h4" gutterBottom>
          Choose Your Plan
        </Typography>
        <Typography variant="body1" color="text.secondary">
          Upgrade to Premium to unlock advanced features and take your practice to the next level
        </Typography>
      </Box>

      {/* Pricing Cards */}
      <Grid container spacing={3} justifyContent="center" mb={4}>
        {pricingPlans.map((plan, index) => (
          <Grid item xs={12} md={5} key={index}>
            <Card 
              sx={{ 
                position: 'relative',
                border: plan.popular ? '2px solid #ff9800' : '1px solid #e0e0e0',
                transform: plan.popular ? 'scale(1.05)' : 'scale(1)',
                transition: 'transform 0.2s ease-in-out',
              }}
            >
              {plan.popular && (
                <Chip
                  label="Most Popular"
                  color="warning"
                  sx={{
                    position: 'absolute',
                    top: -12,
                    left: '50%',
                    transform: 'translateX(-50%)',
                  }}
                />
              )}
              
              <CardContent sx={{ p: 3 }}>
                <Box textAlign="center" mb={3}>
                  <Typography variant="h5" fontWeight="bold" gutterBottom>
                    {plan.name}
                  </Typography>
                  <Typography variant="h3" fontWeight="bold" color="primary">
                    {plan.price}
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    {plan.period}
                  </Typography>
                  <Typography variant="body2" sx={{ mt: 1 }}>
                    {plan.description}
                  </Typography>
                </Box>

                <List>
                  {plan.features.map((feature, idx) => (
                    <ListItem key={idx} sx={{ py: 0.5 }}>
                      <ListItemIcon sx={{ minWidth: 32 }}>
                        <CheckIcon color="success" fontSize="small" />
                      </ListItemIcon>
                      <ListItemText
                        primary={feature}
                        primaryTypographyProps={{ variant: 'body2' }}
                      />
                    </ListItem>
                  ))}
                  
                  {plan.limitations.map((limitation, idx) => (
                    <ListItem key={idx} sx={{ py: 0.5 }}>
                      <ListItemIcon sx={{ minWidth: 32 }}>
                        <CloseIcon color="disabled" fontSize="small" />
                      </ListItemIcon>
                      <ListItemText
                        primary={limitation}
                        primaryTypographyProps={{ 
                          variant: 'body2',
                          sx: { textDecoration: 'line-through', color: 'text.disabled' }
                        }}
                      />
                    </ListItem>
                  ))}
                </List>

                <Button
                  fullWidth
                  variant={plan.buttonVariant}
                  size="large"
                  startIcon={plan.popular ? <UpgradeIcon /> : <StarIcon />}
                  sx={{ mt: 2 }}
                >
                  {plan.buttonText}
                </Button>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>

      {/* Feature Comparison Table */}
      <Paper sx={{ p: 3 }}>
        <Typography variant="h6" gutterBottom>
          Feature Comparison
        </Typography>
        
        {features.map((category, categoryIndex) => (
          <Box key={categoryIndex} mb={3}>
            <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
              {category.category}
            </Typography>
            
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <Typography variant="body2" fontWeight="bold" color="text.secondary">
                  Feature
                </Typography>
              </Grid>
              <Grid item xs={12} md={4}>
                <Typography variant="body2" fontWeight="bold" color="text.secondary" textAlign="center">
                  Lite
                </Typography>
              </Grid>
              <Grid item xs={12} md={4}>
                <Typography variant="body2" fontWeight="bold" color="text.secondary" textAlign="center">
                  Premium
                </Typography>
              </Grid>
            </Grid>
            
            {category.items.map((item, itemIndex) => (
              <Grid container spacing={2} key={itemIndex} sx={{ py: 1 }}>
                <Grid item xs={12} md={4}>
                  <Typography variant="body2">
                    {item.name}
                  </Typography>
                </Grid>
                <Grid item xs={12} md={4} textAlign="center">
                  {renderFeatureIcon(item.lite)}
                </Grid>
                <Grid item xs={12} md={4} textAlign="center">
                  {renderFeatureIcon(item.premium)}
                </Grid>
              </Grid>
            ))}
            
            {categoryIndex < features.length - 1 && <Divider sx={{ my: 2 }} />}
          </Box>
        ))}
      </Paper>

      {/* Upgrade Benefits */}
      <Box mt={4} textAlign="center">
        <Typography variant="h6" gutterBottom>
          Why Upgrade to Premium?
        </Typography>
        <Grid container spacing={2} justifyContent="center">
          <Grid item xs={12} md={3}>
            <Paper sx={{ p: 2, textAlign: 'center' }}>
              <StarIcon color="primary" sx={{ fontSize: 40, mb: 1 }} />
              <Typography variant="subtitle1" fontWeight="bold">
                Advanced Features
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Unlock powerful tools for better patient management
              </Typography>
            </Paper>
          </Grid>
          <Grid item xs={12} md={3}>
            <Paper sx={{ p: 2, textAlign: 'center' }}>
              <UpgradeIcon color="primary" sx={{ fontSize: 40, mb: 1 }} />
              <Typography variant="subtitle1" fontWeight="bold">
                Priority Support
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Get help when you need it with dedicated support
              </Typography>
            </Paper>
          </Grid>
          <Grid item xs={12} md={3}>
            <Paper sx={{ p: 2, textAlign: 'center' }}>
              <CheckIcon color="primary" sx={{ fontSize: 40, mb: 1 }} />
              <Typography variant="subtitle1" fontWeight="bold">
                HIPAA Compliance
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Ensure your practice meets all compliance requirements
              </Typography>
            </Paper>
          </Grid>
        </Grid>
      </Box>
    </Box>
  );
};

export default LiteVsPremium;
