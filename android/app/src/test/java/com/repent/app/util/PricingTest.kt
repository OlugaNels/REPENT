package com.repent.app.util

import org.junit.Assert.assertEquals
import org.junit.Test

class PricingTest {
    @Test fun `shipping is R95 below R800`() {
        assertEquals(95.0, Pricing.shipping(280.0), 0.001)
        assertEquals(95.0, Pricing.shipping(799.99), 0.001)
    }

    @Test fun `shipping is free from R800`() {
        assertEquals(0.0, Pricing.shipping(800.0), 0.001)
        assertEquals(0.0, Pricing.shipping(1120.0), 0.001)
    }

    @Test fun `an empty cart has no shipping`() {
        assertEquals(0.0, Pricing.shipping(0.0), 0.001)
        assertEquals(0.0, Pricing.total(0.0), 0.001)
    }

    @Test fun `total adds shipping`() {
        assertEquals(375.0, Pricing.total(280.0), 0.001)    // matches the API test: 1 cap = R375
        assertEquals(1120.0, Pricing.total(1120.0), 0.001)  // 4 caps, free shipping
    }

    @Test fun `rand formatting`() {
        assertEquals("R350", Pricing.rand(350.0))
        assertEquals("R0", Pricing.rand(0.0))
        assertEquals("R349.50", Pricing.rand(349.5))
        assertEquals("R12.05", Pricing.rand(12.05))
        assertEquals("R1120", Pricing.rand(1120.0))
    }
}
